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

	/**
	 * Tests that the plugin is running in production.
	 */
	public function test_correct_app_env() {
		$this->assertSame($_ENV['APP_ENV'], 'production');
	}
}
