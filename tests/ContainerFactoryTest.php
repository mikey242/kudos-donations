<?php
/**
 * ContainerFactory tests
 */

use IseardMedia\Kudos\ContainerFactory;
use Psr\Container\ContainerInterface;

/**
 * @covers \IseardMedia\Kudos\ContainerFactory
 */
class ContainerFactoryTest extends WP_UnitTestCase {

    /**
     * @throws Exception
     */
    public function test_container_created(): void {
        $container = ContainerFactory::create();
        $this->assertInstanceOf( ContainerInterface::class, $container );
    }
}