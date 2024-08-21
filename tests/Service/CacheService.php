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
		\IseardMedia\Kudos\Service\CacheService::recursively_clear_cache();
		$this->assertSame( false, file_exists( $this->file ) );
	}
}
