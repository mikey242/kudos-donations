<?php
/**
 * Queues and registers settings.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\Handler;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\ActivationAwareInterface;
use IseardMedia\Kudos\Container\HasSettingsInterface;

class SettingsHandler extends AbstractRegistrable implements ActivationAwareInterface {

	public const GROUP             = 'kudos-donations';
	public const HOOK_GET_SETTINGS = 'kudos_get_settings';
	private array $settings        = [];

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action(): string {
		return 'init';
	}

	/**
	 * {@inheritDoc}
	 */
	public function on_plugin_activation(): void {
		$settings = $this->get_all_settings();
		foreach ( $settings as $name => $setting ) {
			if ( get_option( $name, 'not-set' ) === 'not-set' ) {
				update_option( $name, $setting['default'] ?? null );
			}
		}
	}

	/**
	 * Add Settings to array.
	 *
	 * @param HasSettingsInterface $settings Service.
	 */
	public function add( HasSettingsInterface $settings ): void {
		// Call the get_settings() method on the referenced service.
		$this->settings = array_merge( $this->settings, $settings->get_settings() );
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
