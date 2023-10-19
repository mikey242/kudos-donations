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

use DI\Container;
use Exception;
use IseardMedia\Kudos\Infrastructure\Delayed;
use IseardMedia\Kudos\Infrastructure\PluginUninstallAware;
use IseardMedia\Kudos\Infrastructure\Registrable;
use IseardMedia\Kudos\Service\ActivatorService;
use IseardMedia\Kudos\Service\MigratorService;
use Psr\Log\LoggerInterface;
use function add_action;
use function load_plugin_textdomain;

class Plugin {
	private LoggerInterface $logger;
	private Container $container;
	private ActivatorService $activator_service;
	private MigratorService $migrator_service;

	/**
	 * Plugin constructor.
	 *
	 * @param Container        $container The container.
	 * @param LoggerInterface  $logger Instance of logger.
	 * @param ActivatorService $activator_service  Activation related functions.
	 * @param MigratorService  $migrator_service  Service for checking migrations.
	 */
	public function __construct(
		Container $container,
		LoggerInterface $logger,
		ActivatorService $activator_service,
		MigratorService $migrator_service
	) {
		$this->container         = $container;
		$this->logger            = $logger;
		$this->activator_service = $activator_service;
		$this->migrator_service  = $migrator_service;
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
		$database = $this->migrator_service->check_database();
		if ( ! $database ) {
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
	public function on_plugin_uninstall(): void {
		try {
			foreach ( $this->container->getKnownEntryNames() as $entry ) {
				$service = $this->container->get( $entry );

				if ( $service instanceof PluginUninstallAware ) {
					$service->on_plugin_uninstall();
				}
			}
		} catch ( Exception $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**
	 * Loading our plugin's text domain and letting WordPress know where to find its translations.
	 */
	protected function setup_localization(): void {
		add_action(
			'init',
			static function (): void {
				load_plugin_textdomain( 'kudos-donations', false, 'kudos-donations/languages' );
			}
		);
	}

	/**
	 * Instantiate the services.
	 */
	private function instantiate_services(): void {
		try {
			// Loop through definitions and find registrable classes.
			foreach ( $this->container->getKnownEntryNames() as $entry ) {
				if ( ! is_a( $entry, Registrable::class, true ) ) {
					continue;
				}
				/**
				 * Run register method using specified action(s) and priority.
				 *
				 * @var Registrable $registrable
				 */
				$registrable = $this->container->get( $entry );

				// Skip if service not enabled.
				if ( ! $registrable->is_enabled() ) {
					continue;
				}

				// Add specified action or call register directly.
				if ( is_a( $registrable, Delayed::class, true ) ) {
					foreach ( $registrable::get_registration_actions() as $action ) {
						add_action(
							$action,
							[ $registrable, 'register' ],
							$registrable::get_registration_action_priority()
						);
					}
				} else {
					$registrable->register();
				}
			}
		} catch ( Exception $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**
	 * Register the plugin.
	 */
	public function register(): void {
		try {
			do_action( 'kudos_donations_loaded', $this->container );
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
