<?php

/**
 * Serve plugin assets via a hashed filename.
 *
 * Checks for a hashed filename as a value in a JSON object.
 * If it exists: the hashed filename is enqueued in place of the asset name.
 * Fallback: the default asset name will be passed through.
 *
 * @source https://danielshaw.co.nz/wordpress-cache-busting-json-hash-map/
 * @param string $asset e.g style.css
 * @return string
 */

function get_asset_path( $asset ) {

	$map = plugin_dir_path( dirname( __FILE__ ) ) . 'dist/manifest.json';
	$hash = file_exists( $map ) ? json_decode( file_get_contents( $map ), true ) : [];

	if ( array_key_exists( $asset, $hash ) ) {
		return plugin_dir_url( dirname( __FILE__ ) ) . 'dist/' . $hash[ $asset ];
	}

	return $asset;

}