<?php
/**
 * Queues and runs on_plugin_upgrade on relevant services.
 *
 * @link https://code.iseard.media/michael/kudos-donations
 *
 * @copyright 2026 Iseard Media
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
	 * The upgrade aware services. A lazy iterable from the container: services are only
	 * built when it is iterated, which happens inside the upgrader_process_complete callback.
	 *
	 * @var iterable<UpgradeAwareInterface>
	 */
	protected iterable $services;

	/**
	 * Receives the upgrade aware services.
	 *
	 * @param iterable $services Lazy iterable of UpgradeAwareInterface services.
	 */
	public function __construct( iterable $services ) {
		$this->services = $services;
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
