<?php
/**
 * CacheService.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

namespace IseardMedia\Kudos\Service;

use FilesystemIterator;
use IseardMedia\Kudos\Container\UpgradeAwareInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CacheService implements UpgradeAwareInterface, LoggerAwareInterface {

	use LoggerAwareTrait;

	/**
	 * Clears the cache when the plugin is upgraded.
	 */
	public function on_plugin_upgrade(): void {
		$this->purge_cache( null, __( 'Plugin upgraded', 'kudos-donations' ) );
	}

	/**
	 * Purges the plugin cache.
	 *
	 * @param string|null $dir The subdirectory to clear.
	 * @param string      $reason The reason this purge was requested.
	 */
	public function purge_cache( ?string $dir = null, string $reason = '' ): void {
		$context = [];
		$result  = $this->recursively_clear_cache( $dir );
		if ( $dir ) {
			$context['dir'] = $dir;
		}
		if ( $reason ) {
			$context['reason'] = $reason;
		}
		$context['success'] = $result;
		$this->logger->info( 'Plugin cache cleared', $context );
	}

	/**
	 * Clears the container cache folder.
	 *
	 * @param string|null $dir The directory containing the cache.
	 * @return bool True on success, false on failure.
	 */
	private function recursively_clear_cache( ?string $dir = null ): bool {

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
