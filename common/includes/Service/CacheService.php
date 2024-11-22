<?php
/**
 * CacheService.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

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
		$this->recursively_clear_cache();
	}

	/**
	 * Clears the container cache folder.
	 *
	 * @param string|null $dir The directory containing the cache.
	 * @return bool True on success, false on failure.
	 */
	public static function recursively_clear_cache( ?string $dir = null ): bool {

		$target = KUDOS_CACHE_DIR . $dir;
		if ( ! is_dir( $target ) ) {
			return false;
		}

		// Ensure the WP_Filesystem is loaded.
		if ( ! \function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();
		global $wp_filesystem;

		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $target, FilesystemIterator::SKIP_DOTS ),
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
