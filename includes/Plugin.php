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

use IseardMedia\Kudos\Container\Handler\ActivationHandler;
use IseardMedia\Kudos\Container\Handler\RegistrableHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use function add_action;
use function load_plugin_textdomain;

class Plugin implements LoggerAwareInterface {

	use LoggerAwareTrait;

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
		$this->registrable_handler->process();
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
		$this->logger->info(
			'Plugin deactivated',
			[
				'version'      => KUDOS_VERSION,
				'network_wide' => $network_wide,
			]
		);
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
	 * Register the plugin.
	 */
	public function register(): void {
		try {
			do_action( 'kudos_container_ready' );
			$this->on_plugin_loaded();
			do_action( 'kudos_donations_loaded' );
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
