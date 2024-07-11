<?php
/**
 * Plugin tests
 */

/**
 * Sample test case.
 */
class PluginFactory extends WP_UnitTestCase {

	public function test_plugin_created(): void {
		$plugin = \IseardMedia\Kudos\PluginFactory::create();
		$this->assertInstanceOf( \IseardMedia\Kudos\Plugin::class, $plugin );
	}
}
