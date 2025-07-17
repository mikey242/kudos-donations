<?php
/**
 * Plugin tests
 */

use IseardMedia\Kudos\Plugin;
use IseardMedia\Kudos\PluginFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @covers \IseardMedia\Kudos\PluginFactory
 */
class PluginFactoryTest extends WP_UnitTestCase {

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function test_plugin_created(): void {
		$plugin = PluginFactory::create();
		$this->assertInstanceOf( Plugin::class, $plugin );
	}
}
