<?php

namespace IseardMedia\Kudos\Tests;

use IseardMedia\Kudos\Domain\Repository\RepositoryAwareInterface;
use IseardMedia\Kudos\Domain\Repository\RepositoryAwareTrait;
use IseardMedia\Kudos\ContainerFactory;
use IseardMedia\Kudos\Notice\NoticeManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use WP_UnitTestCase;

abstract class BaseTestCase extends WP_UnitTestCase implements RepositoryAwareInterface {

    use RepositoryAwareTrait;

    private ContainerInterface $container;

    /**
     * Set up each test and truncate custom plugin tables.
     */
    public function set_up(): void {
        parent::set_up();
        try {
            $this->container = ContainerFactory::create();
        } catch ( RuntimeException | ContainerExceptionInterface $e ) {
            error_log($e->getMessage());
        }
    }

    /**
     * Tear down after each test.
     *
     * Producers (e.g. PaymentProviderFactory::register) add the kudos_notices filter to WP's
     * global hooks; the test framework does not remove hooks added mid-test, so clear it to stop
     * it leaking into later tests.
     */
    public function tear_down(): void {
        remove_all_filters( NoticeManager::FILTER_NOTICES );
        parent::tear_down();
    }

    protected function get_from_container(string $class) {
        try {
            return $this->container->get($class);
        } catch ( NotFoundExceptionInterface | ContainerExceptionInterface $e ) {
            error_log($e->getMessage());
        }
        return null;
    }

    /**
     * Helper to assert provided string is a valid URL.
     */
    protected function assertValidUrl(string $url): void {
        $this->assertNotFalse(
            filter_var($url, FILTER_VALIDATE_URL),
            "Invalid URL: $url"
        );
    }
}
