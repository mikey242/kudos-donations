<?php
/**
 * Queues and runs on_plugin_upgrade on relevant services.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\Handler;

use IseardMedia\Kudos\Container\UpgradeAwareInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Upgrade handler class.
 */
class UpgradeHandler implements LoggerAwareInterface {

	use LoggerAwareTrait;

	/**
	 * Array of services.
	 *
	 * @var UpgradeAwareInterface[]
	 */
	protected array $services = [];

	/**
	 * Receives the classes with settings.
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
	 * @param UpgradeAwareInterface $service Service.
	 */
	public function add( UpgradeAwareInterface $service ): void {
		$this->services[] = $service;
	}

	/**
	 * Runs on_plugin_upgrade on each service.
	 */
	public function process(): void {
		foreach ( $this->services as $service ) {
			add_action(
				'upgrader_process_complete',
				function ( $upgrader, $hook_extra ) use ( $service ) {
					if ( 'update' === $hook_extra['action'] && 'plugin' === $hook_extra['type'] && isset( $hook_extra['plugins'] ) ) {
						foreach ( $hook_extra['plugins'] as $plugin ) {
							if ( str_contains( $plugin, 'kudos-donations.php' ) ) {
								$this->logger->debug( 'Kudos Donations upgrade detected.', [ 'plugin' => $plugin ] );
								do_action( 'kudos_donations_upgraded', $plugin );
								$service->on_plugin_upgrade();
							}
						}
					}
				},
				10,
				2
			);
		}
	}
}
