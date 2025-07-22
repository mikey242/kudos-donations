<?php
/**
 * Queues and instantiates provided services.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
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
	 * Receives the registrable services.
	 *
	 * @param iterable $services Array of services.
	 */
	public function __construct( iterable $services ) {
		foreach ( $services as $service ) {
			$this->add( $service );
		}
	}

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
	 *
	 * @psalm-suppress UndefinedMethod
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
			if ( $service instanceof Delayed ) {
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
