<?php
/**
 * Queues and runs on_plugin_upgrade on relevant services.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\Handler;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\UpgradeAwareInterface;

/**
 * Upgrade handler class.
 */
class UpgradeHandler extends AbstractRegistrable {

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
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->process();
	}

	/**
	 * Runs on_plugin_upgrade on each service.
	 */
	public function process(): void {
		add_action(
			'upgrader_process_complete',
			function ( \WP_Upgrader $upgrader, array $hook_extra ) {
				if ( 'update' !== $hook_extra['action'] || 'plugin' !== $hook_extra['type'] || ! isset( $hook_extra['plugins'] ) ) {
					return;
				}
				foreach ( $hook_extra['plugins'] as $plugin ) {
					if ( false !== strpos( $plugin, 'kudos-donations.php' ) ) {
						$this->get_logger()->debug( 'Kudos Donations upgrade detected.', [ 'plugin' => $plugin ] );
						do_action( 'kudos_donations_upgraded', $plugin );
						foreach ( $this->services as $service ) {
							$service->on_plugin_upgrade();
						}
						break;
					}
				}
			},
			10,
			2
		);
	}
}
