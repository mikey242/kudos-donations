<?php
/**
 * Queues and instantiates provided services.
 *
 * @link https://code.iseard.media/michael/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\Handler;

use IseardMedia\Kudos\Container\Delayed;
use IseardMedia\Kudos\Container\Registrable;
use IseardMedia\Kudos\Container\SafeLoggerTrait;
use IseardMedia\Kudos\ThirdParty\Symfony\Component\DependencyInjection\ServiceLocator;
use Psr\Log\LoggerAwareInterface;
use Throwable;

/**
 * Hooks each registrable service onto its registration action.
 *
 * Services are resolved from the locator only when their registration action fires, so a
 * request only builds the services (and dependency graphs) it actually uses. For example,
 * REST controllers are never built outside REST requests, nor admin pages on the front end.
 */
class RegistrableHandler implements LoggerAwareInterface {

	use SafeLoggerTrait;

	private ServiceLocator $services;

	/**
	 * Receives the registrable services.
	 *
	 * @param ServiceLocator $services Locator of Registrable services, keyed by service id.
	 */
	public function __construct( ServiceLocator $services ) {
		$this->services = $services;
	}

	/**
	 * Hooks each service's registration onto its action, or registers it now if not Delayed.
	 *
	 * The registration action is read statically from the class, so no service is built here.
	 */
	public function process(): void {
		foreach ( $this->services->getProvidedServices() as $id => $type ) {
			$class = ltrim( (string) $type, '?' );
			if ( '' === $class ) {
				$class = (string) $id;
			}

			if ( is_a( $class, Delayed::class, true ) ) {
				add_action(
					$class::get_registration_action(),
					fn() => $this->register_service( (string) $id ),
					$class::get_registration_action_priority()
				);
			} else {
				$this->register_service( (string) $id );
			}
		}
	}

	/**
	 * Builds the service and registers it if enabled.
	 *
	 * A service that fails to build is logged and skipped, rather than taking the request down:
	 * this runs inside a hook, outside the try/catch around plugin bootstrap.
	 *
	 * @param string $id The service id.
	 */
	private function register_service( string $id ): void {
		try {
			/** @var Registrable $service */
			$service = $this->services->get( $id );
		} catch ( Throwable $e ) {
			$this->get_logger()->error(
				'Failed to build registrable service.',
				[
					'service' => $id,
					'error'   => $e->getMessage(),
				]
			);
			return;
		}

		if ( $service->is_enabled() ) {
			$service->register();
		}
	}
}
