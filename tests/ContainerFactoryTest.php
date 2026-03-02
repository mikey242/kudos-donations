<?php
/**
 * Plugin tests
 */

use IseardMedia\Kudos\Plugin;
use IseardMedia\Kudos\ContainerFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @covers \IseardMedia\Kudos\ContainerFactory
 */
class ContainerFactoryTest extends WP_UnitTestCase {

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function test_plugin_created(): void {
		$plugin = ContainerFactory::create();
		$this->assertInstanceOf( Plugin::class, $plugin );
	}
}
