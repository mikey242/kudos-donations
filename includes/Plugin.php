<?php
/**
 * Main Plugin class.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos;

use Exception;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Infrastructure\Container\ServiceInstantiator;
use IseardMedia\Kudos\Service\ActivatorService;
use IseardMedia\Kudos\Service\MigratorService;
use Psr\Log\LoggerInterface;
use function add_action;
use function load_plugin_textdomain;

class Plugin {
	private LoggerInterface $logger;
	private ActivatorService $activator_service;
	private MigratorService $migrator_service;
	private ServiceInstantiator $service_instantiator;

	/**
	 * Plugin constructor.
	 *
	 * @param LoggerInterface     $logger Instance of logger.
	 * @param ServiceInstantiator $service_instantiator Service instantiator.
	 * @param ActivatorService    $activator_service  Activation related functions.
	 * @param MigratorService     $migrator_service  Service for checking migrations.
	 */
	public function __construct(
		LoggerInterface $logger,
		ServiceInstantiator $service_instantiator,
		ActivatorService $activator_service,
		MigratorService $migrator_service
	) {
		$this->logger               = $logger;
		$this->service_instantiator = $service_instantiator;
		$this->activator_service    = $activator_service;
		$this->migrator_service     = $migrator_service;
	}

	/**
	 * Initialize the services.
	 */
	public function on_plugin_loaded(): void {
		$this->setup_localization();
		if ( $this->plugin_ready() ) {
			$this->instantiate_services();
		}
	}

	/**
	 * Runs checks to ensure plugin ready to run.
	 */
	private function plugin_ready(): bool {
		$skip_migration = $this->migrator_service->check_database();
		if ( ! $skip_migration ) {
			return false;
		}
		return true;
	}

	/**
	 * Runs on plugin activation.
	 *
	 * @param bool $network_wide Whether activation is network-wide or not.
	 */
	public function on_plugin_activation( bool $network_wide ): void {

		// Clear container cache.
		$cache_dir = wp_upload_dir()['basedir'] . '/kudos-donations/container/';
		Utils::recursively_clear_cache( $cache_dir );

		// Activate.
		$this->activator_service->activate( $network_wide );
	}

	/**
	 * Runs on plugin deactivation.
	 *
	 * @param bool $network_wide Whether activation is network-wide or not.
	 */
	public function on_plugin_deactivation( bool $network_wide ): void {
		$this->logger->debug( ' Plugin deactivated', [ 'network_wide' => $network_wide ] );
	}

	/**
	 * Act on plugin uninstall.
	 */
	public function on_plugin_uninstall(): void {}

	/**
	 * Loading our plugin's text domain and letting WordPress know where to find its translations.
	 */
	protected function setup_localization(): void {
		add_action(
			'init',
			static function (): void {
				load_plugin_textdomain( 'kudos-donations', false, \dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			}
		);
	}

	/**
	 * Instantiate the services.
	 */
	private function instantiate_services(): void {
		try {
			// Instantiate services.
			$service_instantiator = $this->service_instantiator;
			$service_instantiator->process();
		} catch ( Exception $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**
	 * Register the plugin.
	 */
	public function register(): void {
		try {
			$this->on_plugin_loaded();
		} catch ( \Throwable $e ) {
			$this->logger->error(
				$e->getMessage(),
				[
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]
			);
		}
	}
}
