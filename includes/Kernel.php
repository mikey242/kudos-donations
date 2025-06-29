<?php
/**
 * Kernel for creating the container.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
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
	 * Uses a hash of the plugin version as part of the filename to ensure old dumped containers not used.
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
			$this->container_builder->compile( true );
			$this->dump_container( $container_file_path );
			$this->container         = $this->container_builder;
			$this->container_builder = null;
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
		$config_paths = apply_filters( 'kudos_container_config_paths', [ $this->get_config_path() ] );
		foreach ( $config_paths as $config_path ) {
			// Ensure the config path ends with a directory separator.
			$config_path = rtrim( $config_path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

			// Create a PhpFileLoader instance for each config path.
			$loader = new PhpFileLoader( $this->container_builder, new FileLocator( $config_path ) );

			// Load the 'services.php' from the current config directory.
			try {
				$loader->load( 'services.php' );
			} catch ( Exception $e ) {
				// phpcs:disable WordPress.PHP.DevelopmentFunctions
				error_log( $e->getMessage() );
				NoticeService::notice( $e->getMessage(), NoticeService::ERROR );
			}
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
