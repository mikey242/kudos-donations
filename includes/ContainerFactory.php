<?php
/**
 * ContainerFactory class.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos;

use Exception;
use IseardMedia\Kudos\Service\CacheService;
use IseardMedia\Kudos\Service\NoticeService;
use IseardMedia\Kudos\ThirdParty\Symfony\Component\Config\FileLocator;
use IseardMedia\Kudos\ThirdParty\Symfony\Component\DependencyInjection\ContainerBuilder;
use IseardMedia\Kudos\ThirdParty\Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use IseardMedia\Kudos\ThirdParty\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Psr\Container\ContainerInterface;
use RuntimeException;
use WP_Filesystem_Base;

class ContainerFactory {

	private ?ContainerInterface $container   = null;
	private ?WP_Filesystem_Base $file_system = null;
	private string $cache_folder;

	/**
	 * @throws Exception Thrown in load_config.
	 *
	 * @param bool $use_cache Whether to load the container from cache.
	 */
	private function __construct( bool $use_cache ) {
		$this->cache_folder = (string) apply_filters( 'kudos_container_cache_dir', KUDOS_CACHE_DIR . 'container/' );

		if ( $use_cache && $this->load_cached_container() ) {
			return;
		}

		$builder = new ContainerBuilder();
		$this->build_container( $builder, $use_cache );
	}

	/**
	 * Build and return the DI container.
	 *
	 * @throws RuntimeException | Exception If the container could not be built.
	 *
	 * @return ContainerInterface The compiled container.
	 */
	public static function create(): ContainerInterface {
		static $container = null;

		if ( null !== $container ) {
			return $container;
		}

		$factory = new self( ! KUDOS_ENV_IS_DEVELOPMENT );

		if ( null === $factory->container ) {
			throw new RuntimeException( 'Error fetching container' );
		}

		$container = $factory->container;
		return $container;
	}

	/**
	 * Gets the name for the container cache file.
	 * Uses a hash of the plugin version to ensure stale containers are not reused.
	 */
	private function get_cache_file(): string {
		$hash_source = (string) apply_filters( 'kudos_container_hash_string', KUDOS_VERSION . $this->get_config_path() );

		return 'container-' . wp_hash( $hash_source ) . '.php';
	}

	/**
	 * Compiles the container and optionally writes it to the cache.
	 *
	 * @throws Exception Thrown in load_config.
	 *
	 * @param ContainerBuilder $builder   The container builder instance.
	 * @param bool             $use_cache Whether to write the compiled container to cache.
	 */
	private function build_container( ContainerBuilder $builder, bool $use_cache ): void {
		$this->load_config( $builder );
		$builder->compile( true );
		$this->container = $builder;

		if ( $use_cache ) {
			$this->initialize_filesystem();
			if ( ! KUDOS_ENV_IS_DEVELOPMENT ) {
				CacheService::recursively_clear_cache( 'container' );
			}
			$this->dump_container( $builder, $this->cache_folder . $this->get_cache_file() );
		}
	}

	/**
	 * Attempts to load the compiled container from cache.
	 */
	private function load_cached_container(): bool {
		$path = $this->cache_folder . $this->get_cache_file();

		if ( ! file_exists( $path ) ) {
			return false;
		}

		// Check no existing instances of KudosContainer exist (unlikely) before requiring ours.
		if ( ! class_exists( 'KudosContainer', false ) ) {
			require_once $path;
		}

		if ( ! class_exists( 'KudosContainer' ) ) {
			return false;
		}

		/** @var ContainerInterface $container */
		$container       = new \KudosContainer();
		$this->container = $container;
		return true;
	}

	/**
	 * Initialises the WP filesystem and creates the cache folder.
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
	 * Loads service configuration from each config path.
	 *
	 * @throws Exception Thrown if unable to load the config file.
	 *
	 * @param ContainerBuilder $builder The container builder instance.
	 */
	private function load_config( ContainerBuilder $builder ): void {
		$config_paths = (array) apply_filters( 'kudos_container_config_paths', [ $this->get_config_path() ] );
		foreach ( $config_paths as $config_path ) {
			$config_path = rtrim( $config_path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
			$loader      = new PhpFileLoader( $builder, new FileLocator( $config_path ) );
			$loader->load( 'services.php' );
		}
	}

	/**
	 * Returns the default config directory path.
	 */
	private function get_config_path(): string {
		return KUDOS_PLUGIN_DIR . 'config/';
	}

	/**
	 * Dumps the compiled container to a file for caching.
	 *
	 * @param ContainerBuilder $builder The compiled container builder.
	 * @param string           $path    The file path to write to.
	 */
	private function dump_container( ContainerBuilder $builder, string $path ): void {
		$dumper = new PhpDumper( $builder );
		/** @var string $dump */
		$dump = $dumper->dump( [ 'class' => 'KudosContainer' ] );

		if ( null !== $this->file_system ) {
			if ( ! $this->file_system->put_contents( $path, $dump ) ) {
				NoticeService::notice(
					'Failed to write the container to the cache file. Please ensure that the "wp-content/cache" directory is writable.',
					NoticeService::ERROR,
				);
			}
		}
	}
}
