<?php
/**
 * Kernel for creating the container.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos;

use Exception;
use IseardMedia\Kudos\Container\CompilerPass\ActivationCompilerPass;
use IseardMedia\Kudos\Container\CompilerPass\RegistrableCompilerPass;
use IseardMedia\Kudos\Container\CompilerPass\SettingsCompilerPass;
use IseardMedia\Kudos\Container\CompilerPass\UpgradeAwareCompilerPass;
use IseardMedia\Kudos\Service\CacheService;
use IseardMedia\Kudos\Service\NoticeService;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use WP_Filesystem_Base;

class Kernel {

	private ?ContainerBuilder $container_builder = null;
	private ?ContainerInterface $container       = null;
	private ?WP_Filesystem_Base $file_system;
	private string $cache_folder;

	/**
	 * Kernel constructor.
	 *
	 * @throws Exception Thrown in load_config.
	 */
	public function __construct() {
		$this->initialize_container();
	}

	/**
	 * Gets the name for the container file.
	 */
	private function get_container_file(): string {
		$cached_version_hash = get_option( '_kudos_version_hash' );
		$strings_to_hash     = apply_filters( 'kudos_container_hash_string', KUDOS_VERSION );
		if ( ! $cached_version_hash || hash( 'md5', $strings_to_hash ) !== $cached_version_hash ) {
			$cached_version_hash = hash( 'md5', $strings_to_hash );
			update_option( '_kudos_version_hash', $cached_version_hash );
		}
		return 'container-' . $cached_version_hash . '.php';
	}

	/**
	 * Returns an array of compiler pass classes.
	 *
	 * @return string[]
	 */
	private function get_compiler_passes(): array {
		return [
			ActivationCompilerPass::class,
			RegistrableCompilerPass::class,
			UpgradeAwareCompilerPass::class,
			SettingsCompilerPass::class,
		];
	}

	/**
	 * Create the container.
	 *
	 * @throws Exception Thrown if config could not be loaded.
	 */
	private function initialize_container(): void {
		$this->cache_folder  = $this->get_cache_folder();
		$container_file_path = $this->cache_folder . self::get_container_file();

		// Enable cache if not in development mode.
		if ( $this->is_production() && file_exists( $container_file_path ) ) {
			require_once $container_file_path;
			$this->container = new \KudosContainer();
		} else {
			CacheService::recursively_clear_cache( 'container' );
			$this->container_builder = new ContainerBuilder();
			$this->initialize_filesystem();
			$this->load_config();
			$this->add_compiler_passes();
			$this->container_builder->compile();
			$this->dump_container( $container_file_path );
			$this->container         = $this->container_builder;
			$this->container_builder = null; // Clear the builder reference after compilation.
		}
	}

	/**
	 * Create required folder for dumping the container.
	 */
	private function initialize_filesystem(): void {
		global $wp_filesystem;
		if ( ! \function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}
		WP_Filesystem();
		$this->file_system = $wp_filesystem;
		wp_mkdir_p( $this->cache_folder );
	}

	/**
	 * Get the cache folder path.
	 *
	 * @return string The cache folder path.
	 */
	private function get_cache_folder(): string {
		return KUDOS_CACHE_DIR . 'container/';
	}

	/**
	 * Load the configuration from a file.
	 *
	 * @throws Exception Thrown if unable to load the config file.
	 */
	private function load_config(): void {
		$config_path = $this->get_config_path();
		$loader      = new PhpFileLoader( $this->container_builder, new FileLocator( $config_path ) );
		$loader->load( 'services.php' );
	}

	/**
	 * Get the configuration path.
	 *
	 * @return string The configuration path.
	 */
	private function get_config_path(): string {
		return KUDOS_PLUGIN_DIR . 'config/';
	}

	/**
	 * Add compiler passes to the container.
	 */
	private function add_compiler_passes(): void {
		foreach ( $this->get_compiler_passes() as $compiler_pass ) {
			$this->container_builder->addCompilerPass( new $compiler_pass() );
		}
	}

	/**
	 * Check if the environment is production.
	 *
	 * @return bool True if in production, false otherwise.
	 */
	private function is_production(): bool {
		return ! KUDOS_ENV_IS_DEVELOPMENT;
	}

	/**
	 * Dumps the compiled container to a file for caching purposes.
	 *
	 * @param string $container_file_path The file to dump the container to.
	 */
	private function dump_container( string $container_file_path ): void {
		$dumper         = new PhpDumper( $this->container_builder );
		$container_dump = $dumper->dump( [ 'class' => 'KudosContainer' ] );

		if ( ! $this->file_system->put_contents( $container_file_path, $container_dump ) ) {
			if ( KUDOS_DEBUG ) {
				NoticeService::notice(
					'Failed to write the container to the cache file. Please ensure that the "wp-content/cache" directory is writable.',
					NoticeService::ERROR,
				);
			}
		}
	}

	/**
	 * Return instance of container.
	 *
	 * @return ContainerInterface The container.
	 */
	public function get_container(): ContainerInterface {
		return $this->container;
	}
}