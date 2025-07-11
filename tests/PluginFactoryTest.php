<?php
/**
 * Plugin tests
 */

/**
 * @covers \IseardMedia\Kudos\PluginFactory
 */
class PluginFactoryTest extends WP_UnitTestCase {

	public function test_plugin_created(): void {
		$plugin = \IseardMedia\Kudos\PluginFactory::create();
		$this->assertInstanceOf( \IseardMedia\Kudos\Plugin::class, $plugin );
	}
}
