<?php
/**
 * Queues and registers settings.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\Handler;

use IseardMedia\Kudos\Container\ActivationAwareInterface;
use IseardMedia\Kudos\Container\Delayed;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Container\Registrable;

class SettingsHandler implements Registrable, Delayed, ActivationAwareInterface {

	public const GROUP             = 'kudos-donations';
	public const HOOK_GET_SETTINGS = 'kudos_get_settings';
	private array $settings        = [];

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_actions(): array {
		return [ 'admin_init', 'rest_api_init', 'init' ];
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action_priority(): int {
		return 5;
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_enabled(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function on_plugin_activation(): void {
		$settings = $this->get_all_settings();
		foreach ( $settings as $name => $setting ) {
			if ( ! get_option( $name ) ) {
				update_option( $name, $setting['default'] ?? null );
			}
		}
	}

	/**
	 * Add Settings to array.
	 *
	 * @param HasSettingsInterface $service Service.
	 */
	public function add( HasSettingsInterface $service ): void {
		$this->settings = array_merge( $this->settings, $service->get_settings() );
	}

	/**
	 * Return all the currently added settings.
	 */
	public function get_all_settings(): array {
		return apply_filters( self::HOOK_GET_SETTINGS, $this->settings );
	}

	/**
	 * Registers the settings.
	 */
	public function register(): void {
		$settings = $this->get_all_settings();
		foreach ( $settings as $name => $args ) {
			register_setting(
				self::GROUP,
				$name,
				$args
			);
		}
	}
}
