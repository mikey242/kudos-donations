<?php
/**
 * ContainerFactory tests
 */

namespace IseardMedia\Kudos\Tests;

use Exception;
use IseardMedia\Kudos\ContainerFactory;
use IseardMedia\Kudos\ThirdParty\Symfony\Component\DependencyInjection\ContainerBuilder;
use IseardMedia\Kudos\ThirdParty\Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Psr\Container\ContainerInterface;
use WP_UnitTestCase;

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

    /**
     * @throws Exception
     *
     * Verifies that the container dump produced by PhpDumper is syntactically valid PHP.
     * This catches cases where the DI container dumper generates PHP syntax incompatible
     * with the current PHP version (e.g. PHP 8.0+ union return types on PHP 7.4).
     *
     * ContainerFactory::create() returns the compiled ContainerBuilder when built fresh
     * (WP_Filesystem is unavailable in tests so the cache write is skipped, but the
     * builder itself is returned). We re-dump it here to validate the output.
     */
    public function test_container_dump_is_syntactically_valid(): void {
        if ( PHP_MAJOR_VERSION < 8 ) {
            $this->markTestSkipped( 'Container caching is disabled below PHP 8.0; dump validation not applicable.' );
        }

        $container = ContainerFactory::create();

        if ( ! $container instanceof ContainerBuilder ) {
            $this->markTestSkipped( 'Container was loaded from cache; ContainerBuilder not available.' );
        }

        $dumper = new PhpDumper( $container );
        /** @var string $dump */
        $dump = $dumper->dump( [ 'class' => 'KudosTestContainer' ] );

        $tmp = tempnam( sys_get_temp_dir(), 'kudos-container-' ) . '.php';
        file_put_contents( $tmp, $dump );

        $output    = [];
        $exit_code = 0;
        exec( PHP_BINARY . ' -l ' . escapeshellarg( $tmp ), $output, $exit_code );
        unlink( $tmp );

        $this->assertSame(
            0,
            $exit_code,
            'Container dump has PHP syntax errors: ' . implode( "\n", $output )
        );
    }
}