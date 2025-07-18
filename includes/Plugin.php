<?php
/**
 * Main Plugin class.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos;

use IseardMedia\Kudos\Container\Handler\ActivationHandler;
use IseardMedia\Kudos\Container\Handler\RegistrableHandler;
use IseardMedia\Kudos\Container\SafeLoggerTrait;
use IseardMedia\Kudos\Service\CacheService;
use Psr\Log\LoggerAwareInterface;
use Throwable;

class Plugin implements LoggerAwareInterface {

	use SafeLoggerTrait;

	private ActivationHandler $activation_handler;
	private RegistrableHandler $registrable_handler;

	/**
	 * Plugin constructor.
	 *
	 * @param RegistrableHandler $registrable_handler Registration handler.
	 * @param ActivationHandler  $activation_handler  Activation related functions.
	 */
	public function __construct(
		RegistrableHandler $registrable_handler,
		ActivationHandler $activation_handler
	) {
		$this->registrable_handler = $registrable_handler;
		$this->activation_handler  = $activation_handler;
	}

	/**
	 * Initialize the services.
	 */
	public function on_plugin_loaded(): void {
		$this->setup_localization();
		$this->add_global_localization_data();
		$this->registrable_handler->process();
	}

	/**
	 * Runs on plugin activation.
	 */
	public function on_plugin_activation(): void {
		CacheService::recursively_clear_cache();
		flush_rewrite_rules();
		$this->activation_handler->process();
		$this->logger->info(
			'Plugin activated.',
			[ 'version' => KUDOS_VERSION ]
		);
	}

	/**
	 * Runs on plugin deactivation.
	 */
	public function on_plugin_deactivation(): void {
		$this->logger->info(
			'Plugin deactivated',
			[ 'version' => KUDOS_VERSION ]
		);
	}

	/**
	 * Act on plugin uninstall.
	 */
	public function on_plugin_uninstall(): void {}

	/**
	 * Loading our plugin's text domain and letting WordPress know where to find its translations.
	 */
	private function setup_localization(): void {
		add_action(
			'init',
			static function (): void {
				load_plugin_textdomain( 'kudos-donations', false, \dirname( plugin_basename( __FILE__ ), 2 ) . '/languages' );
			}
		);
	}

	/**
	 * Add data to the global localization array.
	 */
	private function add_global_localization_data(): void {
		add_filter(
			'kudos_global_localization',
			function ( array $localization ): array {
				$localization['version'] = KUDOS_VERSION;
				return $localization;
			}
		);
	}

	/**
	 * Register the plugin.
	 */
	public function register(): void {
		try {
			do_action( 'kudos_container_ready' );
			$this->on_plugin_loaded();
			do_action( 'kudos_donations_loaded' );
		} catch ( Throwable $e ) {
			$this->get_logger()->error(
				$e->getMessage(),
				[
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]
			);
		}
	}
}
