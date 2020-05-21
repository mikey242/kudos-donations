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
 * @param bool $path
 *
 * @return string
 */

function get_asset_url( $asset, $path=false ) {

	$map = KUDOS_DIR . 'dist/manifest.json';
	$hash = file_exists( $map ) ? json_decode( file_get_contents( $map ), true ) : [];

	if ( array_key_exists( $asset, $hash ) ) {
		if(!$path) {
			return plugin_dir_url( dirname( __FILE__ ) ) . 'dist/' . $hash[ $asset ];
		}
		return KUDOS_DIR . 'dist/' . $hash[ $asset ];
	}

	return $asset;
}

/**
 * Converts three letter currency code into a symbol
 *
 * @since      1.0.2
 * @param $currency
 * @return string
 */
function get_currency_symbol($currency) {

	$currency = strtoupper($currency);

	switch ($currency) {
		case 'EUR':
		    $symbol = '&#8364;';
            break;
		case 'USD':
		    $symbol = '&#36;';
            break;
		case 'GBP':
		    $symbol = '&#163;';
            break;
		case 'JPY':
		    $symbol = '&#165;';
            break;
		default:
		    $symbol = $currency;
	}

	return $symbol;
}