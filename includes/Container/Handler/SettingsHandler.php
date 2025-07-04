<?php
/**
 * Queues and registers settings.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\Handler;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\ActivationAwareInterface;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class SettingsHandler extends AbstractRegistrable implements ActivationAwareInterface {

	public const HOOK_GET_SETTINGS = 'kudos_get_settings';
	private array $settings        = [];
	private string $group;

	/**
	 * Receives a service locator with HasSettingsInterface implementations.
	 *
	 * @param ServiceLocator $service_locator Array of services.
	 * @param string         $group The settings group.
	 */
	public function __construct( ServiceLocator $service_locator, string $group ) {
		$this->group = $group;
		/**
		 * The settings service.
		 *
		 * @var HasSettingsInterface $service
		 */
		foreach ( $service_locator->getProvidedServices() as $service => $name ) {
			$this->settings = array_merge( $this->settings, $service::get_settings() );
		}
	}

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
				$this->group,
				$name,
				$args
			);
		}
	}
}
