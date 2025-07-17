<?php
/**
 * Helper for retrieving assets.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Helper;

class Assets {
	/**
	 * Uses manifest to get asset URL.
	 *
	 * @param string $asset Asset name.
	 * @param bool   $version Whether to attach the version number.
	 */
	public static function get_style( string $asset, bool $version = true ): ?string {
		$suffix = 'build/' . ltrim( $asset, '/' );
		$url    = KUDOS_PLUGIN_URL . $suffix;
		$path   = KUDOS_PLUGIN_DIR . $suffix;

		if ( ! file_exists( $path ) ) {
			return null;
		}
		return $url . ( $version ? '?ver=' . filemtime( $path ) : '' );
	}

	/**
	 * Returns an array with js file properties.
	 * This includes checking for an accompanying .asset.php file.
	 *
	 * @param string $asset The script name.
	 * @param string $base_dir Base dir to search.
	 * @param string $base_url Base use to return.
	 * @return array{
	 *  path: string,
	 *  url: string,
	 *  dependencies: array,
	 *  version: string
	 *  }
	 */
	public static function get_script(
		string $asset,
		string $base_dir = KUDOS_PLUGIN_DIR,
		string $base_url = KUDOS_PLUGIN_URL
	): ?array {
		$asset_path = $base_dir . 'build/' . $asset;
		if ( file_exists( $asset_path ) ) {
			$return         = [];
			$return['path'] = $asset_path;
			$return['url']  = $base_url . 'build/' . ltrim( $asset, '/' );
			$asset_manifest = substr_replace( $asset_path, '.asset.php', -\strlen( '.js' ) );
			if ( file_exists( $asset_manifest ) ) {
				$manifest_content       = include $asset_manifest;
				$return['dependencies'] = $manifest_content['dependencies'] ?? [];
				$return['version']      = $manifest_content['version'] ?? KUDOS_VERSION;
				return $return;
			}
		}

		return null;
	}
}
