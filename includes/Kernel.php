<?php
/**
 * Kernel for creating the container.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos;

use Exception;
use IseardMedia\Kudos\Service\CacheService;
use IseardMedia\Kudos\Service\NoticeService;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use WP_Filesystem_Base;

class Kernel {

	private ContainerBuilder $container_builder;
	private ?ContainerInterface $container   = null;
	private ?WP_Filesystem_Base $file_system = null;
	private bool $use_cache;
	private string $cache_folder;

	/**
	 * Kernel constructor.
	 *
	 * @throws Exception Thrown in load_config.
	 *
	 * @param bool $use_cache Whether to use a cached version of the container.
	 */
	public function __construct( bool $use_cache = false ) {
		$this->use_cache    = $use_cache;
		$this->cache_folder = (string) apply_filters( 'kudos_container_cache_dir', KUDOS_CACHE_DIR . 'container/' );

		if ( $this->use_cache && $this->load_cached_container() ) {
			return;
		}

		$this->container_builder = new ContainerBuilder();

		$this->build_container();
	}

	/**
	 * Gets the name for the container file.
	 * Uses a hash of the plugin version as part of the filename to ensure old dumped containers not used.
	 */
	private function get_container_file(): string {
		$hash_source = (string) apply_filters( 'kudos_container_hash_string', KUDOS_VERSION . $this->get_config_path() );
		$hash        = wp_hash( $hash_source );

		return 'container-' . $hash . '.php';
	}

	/**
	 * Creates the container based on loaded config.
	 *
	 * @throws Exception Thrown in load_config.
	 */
	private function build_container(): void {
		$this->load_config();

		$this->container_builder->compile( true );
		$this->container = $this->container_builder;

		if ( $this->use_cache ) {
			$this->initialize_filesystem();
			if ( ! KUDOS_ENV_IS_DEVELOPMENT ) {
				CacheService::recursively_clear_cache( 'container' );
			}
			$this->dump_container( $this->cache_folder . $this->get_container_file() );
		}
	}

	/**
	 * Load container from cache.
	 */
	private function load_cached_container(): bool {
		$container_file_path = $this->cache_folder . $this->get_container_file();

		// Bail if container file not found.
		if ( ! file_exists( $container_file_path ) ) {
			return false;
		}

		// Check no existing instances of KudosContainer exist (unlikely) before requiring ours.
		if ( ! class_exists( 'KudosContainer', false ) ) {
			require_once $container_file_path;
		}

		// We should now have a KudosContainer class available, bail if not.
		if ( ! class_exists( 'KudosContainer' ) ) {
			return false;
		}

		$this->container = new \KudosContainer();
		return true;
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

		if ( ! $wp_filesystem ) {
			return;
		}

		$this->file_system = $wp_filesystem;
		wp_mkdir_p( $this->cache_folder );
	}

	/**
	 * Load the configuration from a file.
	 *
	 * @throws Exception Thrown if unable to load the config file.
	 */
	private function load_config(): void {
		$config_paths = (array) apply_filters( 'kudos_container_config_paths', [ $this->get_config_path() ] );
		foreach ( $config_paths as $config_path ) {
			// Ensure the config path ends with a directory separator.
			$config_path = rtrim( $config_path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

			// Create a PhpFileLoader instance for each config path.
			$loader = new PhpFileLoader( $this->container_builder, new FileLocator( $config_path ) );

			// Load the 'services.php' from the current config directory.
			$loader->load( 'services.php' );
		}
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
	 * Dumps the compiled container to a file for caching purposes.
	 *
	 * @param string $container_file_path The file to dump the container to.
	 */
	private function dump_container( string $container_file_path ): void {
		$dumper = new PhpDumper( $this->container_builder );
		/** @var string $container_dump */
		$container_dump = $dumper->dump( [ 'class' => 'KudosContainer' ] );

		if ( null !== $this->file_system ) {
			if ( ! $this->file_system->put_contents( $container_file_path, $container_dump ) ) {
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
	 * @return ?ContainerInterface The container.
	 */
	public function get_container(): ?ContainerInterface {
		return $this->container;
	}
}
