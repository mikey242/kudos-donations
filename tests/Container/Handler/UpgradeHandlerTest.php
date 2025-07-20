<?php
/**
 * UpgradeHandler tests.
 */

namespace IseardMedia\Kudos\Tests\Container\Handler;

use Plugin_Upgrader;
use WP_UnitTestCase;

/**
 * @covers \IseardMedia\Kudos\Container\Handler\UpgradeHandler
 */
class UpgradeHandlerTest extends WP_UnitTestCase {

	public function setUp(): void {
		// Include necessary WordPress files to access the classes needed for the upgrade process.
		if ( ! class_exists( 'Plugin_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		// Simulate the data that the hook would normally receive.
		$upgrader   = new Plugin_Upgrader();
		$hook_extra = [
			'action'  => 'update', // Action could be 'install', 'update', or 'delete'.
			'type'    => 'plugin', // Type could be 'plugin', 'theme', 'translation'.
			'bulk'    => false, // If this was a bulk action or not.
			'plugins' => [
				'kudos-donations/kudos-donations.php', // Path to the plugin file.
			],
		];

		// Manually trigger the 'upgrader_process_complete' hook.
		do_action( 'upgrader_process_complete', $upgrader, $hook_extra );
	}

	/**
	 * Test that plugin is registered.
	 */
	public function test_upgrader_process_complete() {
		$this->assertSame( 1, did_action( 'kudos_donations_upgraded' ) );
	}
}
