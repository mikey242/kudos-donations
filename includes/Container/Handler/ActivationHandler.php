<?php
/**
 * Queues and runs on_activation on relevant classes.
 *
 * @link https://code.iseard.media/michael/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\Handler;

use IseardMedia\Kudos\Container\ActivationAwareInterface;

/**
 * ActivationHandler class.
 */
class ActivationHandler {

	/**
	 * The activation aware services. A lazy iterable from the container: services are only
	 * built when it is iterated, so this must not happen before process().
	 *
	 * @var iterable<ActivationAwareInterface>
	 */
	protected iterable $services;

	/**
	 * Receives the activation aware services.
	 *
	 * @param iterable $services Lazy iterable of ActivationAwareInterface services.
	 */
	public function __construct( iterable $services ) {
		$this->services = $services;
	}

	/**
	 * Runs on_plugin_activation on each service.
	 */
	public function process(): void {
		foreach ( $this->services as $service ) {
			$service->on_plugin_activation();
		}
	}
}
