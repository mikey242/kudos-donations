<?php
/**
 * CacheService tests.
 */

namespace Service;

use WP_UnitTestCase;

/**
 * Sample test case.
 */
class CacheService extends WP_UnitTestCase {

	public function setUp(): void {
		/**
		 * Set up cache service.
		 */
		$this->cache = new \IseardMedia\Kudos\Service\CacheService();
		$logger      = $this->createMock( \Psr\Log\LoggerInterface::class );
		$this->cache->setLogger( $logger );

		/**
		 * Create file ad folder in cache directory.
		 */
		$dir = KUDOS_CACHE_DIR . 'test/';
		wp_mkdir_p( $dir );
		$this->file = $dir . 'test.php';
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $this->file, '' );
	}

	/**
	 * Test that plugin is registered.
	 */
	public function test_purge_cache() {
		$this->cache->purge_cache();
		$this->assertSame( false, file_exists( $this->file ) );
	}
}
