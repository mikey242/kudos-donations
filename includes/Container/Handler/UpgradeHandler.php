<?php
/**
 * Queues and runs on_plugin_upgrade on relevant services.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\Handler;

use IseardMedia\Kudos\Container\UpgradeAwareInterface;

/**
 * Upgrade handler class.
 */
class UpgradeHandler {
	/**
	 * Array of services.
	 *
	 * @var UpgradeAwareInterface[]
	 */
	protected array $services = [];

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
