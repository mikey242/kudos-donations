<?php
/**
 * Class SampleTest
 */

/**
 * Sample test case.
 */
class Plugin extends WP_UnitTestCase {

	private \IseardMedia\Kudos\Plugin $plugin;

	public function setUp(): void {
		$this->plugin = \IseardMedia\Kudos\PluginFactory::create();
		parent::setup();
	}

	/**
	 * Test that plugin is registered.
	 */
	public function test_plugin_loaded() {
		$this->assertSame( 1, did_action( 'kudos_donations_loaded' ) );
	}
}
