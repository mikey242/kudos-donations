<?php

namespace Kudos\Helpers;

class Assets {
	/**
	 * Serve plugin assets via a hashed filename.
	 *
	 * Checks for a hashed filename as a value in a JSON object.
	 * If it exists: the hashed filename is enqueued in place of the asset name.
	 * Fallback: the default asset name will be passed through.
	 *
	 * @source https://danielshaw.co.nz/wordpress-cache-busting-json-hash-map/
	 * @param string $url e.g style.css.
	 *
	 * @return array
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
	 * @param string $asset
	 * @param string $url
	 *
	 * @return string
	 */
	public static function get_asset_url( string $asset, string $url = KUDOS_PLUGIN_URL ): string {
		$hash = self::get_asset_manifest( $url );
		if ( isset( $hash[ $asset ] ) ) {
			return $url . 'dist/' . ltrim($hash[ $asset ], '/');
		}

		return $url . 'dist/' . ltrim($asset, '/');
	}

	/**
	 * Returns an array with js file properties.
	 * This includes checking for an accompanying .asset.php file
	 *
	 * @param $asset
	 *
	 * @return array|null
	 */
	public static function get_script($asset): ?array {

		$asset_path = KUDOS_PLUGIN_DIR . '/dist' . $asset;
		if(file_exists($asset_path)) {
			$out = [];
			$out['path'] = $asset_path;
			$out['url'] = KUDOS_PLUGIN_URL . 'dist/' . ltrim($asset, '/');
			$asset_manifest = substr_replace( $asset_path, '.asset.php', - strlen( '.js' ) );
			if(file_exists($asset_manifest)) {
				$manifest_content = require($asset_manifest);
				$out['dependencies'] = $manifest_content['dependencies'] ?? [];
				$out['version'] = $manifest_content['version'] ?? KUDOS_VERSION;
			}

			return $out;
		}

		return null;
	}

	/**
	 * Uses manifest to get asset path.
	 *
	 * @param string $asset
	 * @param string $url
	 *
	 * @return string
	 */
	public static function get_asset_path( string $asset, string $url = KUDOS_PLUGIN_URL ): string {
		$hash = self::get_asset_manifest( $url );
		if ( isset( $hash[ $asset ] ) ) {
			return KUDOS_PLUGIN_DIR . '/dist/' . ltrim($hash[ $asset ], '/');
		}

		return KUDOS_PLUGIN_DIR . '/dist/' . ltrim($asset, '/');
	}

	/**
	 * Get plugin asset content via a hashed filename.
	 *
	 * Checks for a hashed filename as a value in a JSON object.
	 * If it exists: the hashed filename is enqueued in place of the asset name.
	 * Fallback: the default asset name will be passed through.
	 *
	 * @source https://danielshaw.co.nz/wordpress-cache-busting-json-hash-map/
	 * @param string $asset e.g style.css.
	 *
	 * @return string
	 */
	public static function get_asset_content( string $asset ): string {

		$map      = KUDOS_PLUGIN_URL . 'dist/mix-manifest.json';
		$request  = wp_remote_get( $map );
		$response = wp_remote_retrieve_body( $request );
		$hash     = ! empty( $response ) ? json_decode( $response, true ) : [];

		if ( isset( $hash[ $asset ] ) ) {
			$asset_request = wp_remote_get( KUDOS_PLUGIN_URL . 'dist/' . $hash[ $asset ] );

			return wp_remote_retrieve_body( $asset_request );
		}

		return $asset;
	}
}