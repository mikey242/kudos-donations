<?php

namespace IseardMedia\Kudos\Helper;

class Assets {

	/**
	 * Uses manifest to get asset URL.
	 *
	 * @throws \Exception
	 *
	 * @param string $asset
	 * @param bool   $version
	 */
	public static function get_style( string $asset, bool $version = true ): string {
		$suffix = 'build/' . ltrim( $asset, '/' );
		$url    = KUDOS_PLUGIN_URL . $suffix;
		$path   = KUDOS_PLUGIN_DIR . $suffix;

		if ( ! file_exists( $path ) ) {
			throw new \Exception( "Cannot find style '$asset'" );
		}
		return $url . ( $version ? '?ver=' . filemtime( $path ) : '' );
	}

	/**
	 * Returns an array with js file properties.
	 * This includes checking for an accompanying .asset.php file.
	 *
	 * @param string $asset
	 * @param string $base_dir
	 * @param string $base_url
	 *
	 * @return array|null
	 *@throws \Exception
	 *
	 */
	public static function get_script(
		string $asset,
		string $base_dir = KUDOS_PLUGIN_DIR,
		string $base_url = KUDOS_PLUGIN_URL
	): ?array {
		$asset_path = $base_dir . 'build/' . $asset;
		if ( file_exists( $asset_path ) ) {
			$out            = [];
			$out['path']    = $asset_path;
			$out['url']     = $base_url . 'build/' . ltrim( $asset, '/' );
			$asset_manifest = substr_replace( $asset_path, '.asset.php', -\strlen( '.js' ) );
			if ( file_exists( $asset_manifest ) ) {
				$manifest_content    = include $asset_manifest;
				$out['dependencies'] = $manifest_content['dependencies'] ?? [];
				$out['version']      = $manifest_content['version'] ?? KUDOS_VERSION;
			}

			return $out;
		}

		throw new \Exception( "Cannot find script '$asset_path'" );
	}

	/**
	 * Uses manifest to get asset path.
	 *
	 * @param string $asset
	 */
	public static function get_asset_path( string $asset ): string {
		return KUDOS_PLUGIN_DIR . 'build/' . ltrim( $asset, '/' );
	}
}
