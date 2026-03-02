<?php
/**
 * ContainerFactory tests
 */

use IseardMedia\Kudos\ContainerFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @covers \IseardMedia\Kudos\ContainerFactory
 */
class ContainerFactoryTest extends WP_UnitTestCase {

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function test_container_created(): void {
        $container = ContainerFactory::create();
        $this->assertInstanceOf( ContainerInterface::class, $container );
    }
}