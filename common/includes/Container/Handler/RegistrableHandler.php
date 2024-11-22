<?php
/**
 * Queues and instantiates provided services.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\Handler;

use IseardMedia\Kudos\Container\Delayed;
use IseardMedia\Kudos\Container\Registrable;

/**
 * Service instantiator class.
 */
class RegistrableHandler {
	/**
	 * Array of services.
	 *
	 * @var Registrable[]
	 */
	protected array $services = [];

	/**
	 * Add service to list.
	 *
	 * @param Registrable $service Service.
	 */
	public function add( Registrable $service ): void {
		$this->services[] = $service;
	}

	/**
	 * Runs initialize on each service.
	 */
	public function process(): void {
		// Remove disabled services.
		$enabled_services = array_filter(
			$this->services,
			static fn( $service ) => $service->is_enabled()
		);
		// Add 'register' callback function to specified hook.
		foreach ( $enabled_services as $service ) {
			// Add specified action or call register directly.
			if ( is_a( $service, Delayed::class, true ) ) {
				add_action(
					$service::get_registration_action(),
					[ $service, 'register' ],
					$service::get_registration_action_priority()
				);
			} else {
				$service->register();
			}
		}
	}
}
