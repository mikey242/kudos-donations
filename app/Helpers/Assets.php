<?php
/**
 * Asset helper.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

namespace Kudos\Helpers;

class Assets {
	/**
	 * Serve plugin assets via a hashed filename.
	 *
	 * Checks for a hashed filename as a value in a JSON object.
	 * If it exists: the hashed filename is enqueued in place of the asset name.
	 * Fallback: the default asset name will be passed through.
	 *
	 * @param string $url e.g. style.css.
	 *
	 * @source https://danielshaw.co.nz/wordpress-cache-busting-json-hash-map/
	 */
	private static function get_asset_manifest( string $url ): array {
		$map      = $url . 'dist/mix-manifest.json';
		$request  = wp_remote_get( $map );
		$response = wp_remote_retrieve_body( $request );

		return ! empty( $response ) ? json_decode( $response, true ) : [];
	}

	/**
	 * Uses manifest to get asset URL.
	 *
	 * @param string $asset Asset to get.
	 * @param string $url Base url.
	 */
	public static function get_asset_url( string $asset, string $url = KUDOS_PLUGIN_URL ): string {
		$hash = self::get_asset_manifest( $url );
		if ( isset( $hash[ $asset ] ) ) {
			return $url . 'dist/' . ltrim( $hash[ $asset ], '/' );
		}

		return $url . 'dist/' . ltrim( $asset, '/' );
	}

	/**
	 * Returns an array with js file properties.
	 * This includes checking for an accompanying .asset.php file.
	 *
	 * @param string $asset The asset path.
	 * @param string $base_dir The base dir to use.
	 * @param string $base_url The base url to use.
	 */
	public static function get_script(
		string $asset,
		string $base_dir = KUDOS_PLUGIN_DIR,
		string $base_url = KUDOS_PLUGIN_URL
	): ?array {

		$asset_path = $base_dir . '/dist' . $asset;
		if ( file_exists( $asset_path ) ) {
			$out            = [];
			$out['path']    = $asset_path;
			$out['url']     = $base_url . 'dist/' . ltrim( $asset, '/' );
			$asset_manifest = substr_replace( $asset_path, '.asset.php', - \strlen( '.js' ) );
			if ( file_exists( $asset_manifest ) ) {
				$manifest_content    = require $asset_manifest;
				$out['dependencies'] = $manifest_content['dependencies'] ?? [];
				$out['version']      = $manifest_content['version'] ?? KUDOS_VERSION;
			}
			return $out;
		}

		return null;
	}

	/**
	 * Uses manifest to get asset path.
	 *
	 * @param string $asset The asset path.
	 * @param string $url The base url to use.
	 */
	public static function get_asset_path( string $asset, string $url = KUDOS_PLUGIN_URL ): string {
		$hash = self::get_asset_manifest( $url );
		if ( isset( $hash[ $asset ] ) ) {
			return KUDOS_PLUGIN_DIR . '/dist/' . ltrim( $hash[ $asset ], '/' );
		}

		return KUDOS_PLUGIN_DIR . '/dist/' . ltrim( $asset, '/' );
	}
}
