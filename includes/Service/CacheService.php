<?php
/**
 * CacheService.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

use FilesystemIterator;
use IseardMedia\Kudos\Container\UpgradeAwareInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CacheService implements UpgradeAwareInterface {

	/**
	 * Clears the cache when the plugin is upgraded.
	 */
	public function on_plugin_upgrade(): void {
		self::recursively_clear_cache();
	}

	/**
	 * Clears the container cache folder.
	 *
	 * @param string|null $dir The directory containing the cache.
	 * @return bool True on success, false on failure.
	 */
	public static function recursively_clear_cache( ?string $dir = null ): bool {

		$target = KUDOS_CACHE_DIR . $dir;

		// Ensure the target is within the cache directory.
		$real_cache  = realpath( KUDOS_CACHE_DIR );
		$real_target = realpath( $target );
		if ( ! $real_cache || ! $real_target || 0 !== strpos( $real_target, $real_cache ) ) {
			return false;
		}

		if ( ! is_dir( $real_target ) ) {
			return false;
		}

		// Ensure the WP_Filesystem is loaded.
		if ( ! \function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			return false;
		}

		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $real_target, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $files as $file ) {
			if ( ! $wp_filesystem->delete( $file->getRealPath(), true ) ) {
				return false;
			}
		}

		return true;
	}
}
