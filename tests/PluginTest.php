<?php
/**
 * Plugin tests
 */

/**
 * @covers \IseardMedia\Kudos\Plugin
 */
class PluginTest extends WP_UnitTestCase {

	/**
	 * Test that plugin container is created.
	 */
	public function test_container_ready() {
		$this->assertSame( 1, did_action( 'kudos_container_ready' ) );
	}

	/**
	 * Test that plugin container is created.
	 */
	public function test_plugin_loaded() {
		$this->assertSame( 1, did_action( 'kudos_donations_loaded' ) );
	}
}
