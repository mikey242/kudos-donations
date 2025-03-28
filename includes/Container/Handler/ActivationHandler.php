<?php
/**
 * Queues and runs on_activation on relevant classes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\Handler;

use IseardMedia\Kudos\Container\ActivationAwareInterface;

/**
 * ActivationHandler class.
 */
class ActivationHandler {

	/**
	 * Array of services.
	 *
	 * @var ActivationAwareInterface[]
	 */
	protected array $services = [];

	/**
	 * Receives the activation aware services.
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
	 * @param ActivationAwareInterface $service Service.
	 */
	public function add( ActivationAwareInterface $service ): void {
		$this->services[] = $service;
	}

	/**
	 * Runs initialize on each service.
	 */
	public function process(): void {
		foreach ( $this->services as $service ) {
			$service->on_plugin_activation();
		}
	}
}
