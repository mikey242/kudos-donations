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

use IseardMedia\Kudos\Infrastructure\Container\ActivationHandler;
use IseardMedia\Kudos\Infrastructure\Container\ServiceHandler;
use IseardMedia\Kudos\Service\MigratorService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use function add_action;
use function load_plugin_textdomain;

class Plugin implements LoggerAwareInterface {

	use LoggerAwareTrait;

	private ActivationHandler $activation_handler;
	private MigratorService $migrator_service;
	private ServiceHandler $service_handler;

	/**
	 * Plugin constructor.
	 *
	 * @param ServiceHandler    $service_handler Service instantiator.
	 * @param ActivationHandler $activation_handler  Activation related functions.
	 * @param MigratorService   $migrator_service  Service for checking migrations.
	 */
	public function __construct(
		ServiceHandler $service_handler,
		ActivationHandler $activation_handler,
		MigratorService $migrator_service
	) {
		$this->service_handler    = $service_handler;
		$this->activation_handler = $activation_handler;
		$this->migrator_service   = $migrator_service;
	}

	/**
	 * Initialize the services.
	 */
	public function on_plugin_loaded(): void {
		$this->setup_localization();
		if ( $this->is_plugin_ready() ) {
			$this->instantiate_services();
		}
	}

	/**
	 * Runs checks to ensure plugin ready to run.
	 */
	private function is_plugin_ready(): bool {
		$skip_migration = $this->migrator_service->check_database();
		if ( ! $skip_migration ) {
			return false;
		}
		return true;
	}

	/**
	 * Runs on plugin activation.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 */
	public function on_plugin_activation( bool $network_wide ): void {
		$this->activation_handler->process();
		$this->logger->info(
			'Plugin activated.',
			[
				'version'      => KUDOS_VERSION,
				'network_wide' => $network_wide,
			]
		);
	}

	/**
	 * Runs on plugin deactivation.
	 *
	 * @param bool $network_wide Whether deactivation is network-wide or not.
	 */
	public function on_plugin_deactivation( bool $network_wide ): void {
		$this->logger->info( 'Plugin deactivated', [ 'network_wide' => $network_wide ] );
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
		$this->service_handler->process();
	}

	/**
	 * Register the plugin.
	 */
	public function register(): void {
		try {
			do_action( 'kudos_donations_loaded' );
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
