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

/**
 * Lightens/darkens a given colour (hex format), returning the altered colour in hex format.7
 * @param string $hex Colour as hexadecimal (with or without hash);
 * @percent float $percent Decimal ( 0.2 = lighten by 20%(), -0.4 = darken by 40%() )
 * @return string Lightened/Darkend colour as hexadecimal (with hash);
 */
function color_luminance( $hex, $percent ) {

	// validate hex string

	$hex = preg_replace( '/[^0-9a-f]/i', '', $hex );
	$new_hex = '#';

	if ( strlen( $hex ) < 6 ) {
		$hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
	}

	// convert to decimal and change luminosity
	for ($i = 0; $i < 3; $i++) {
		$dec = hexdec( substr( $hex, $i*2, 2 ) );
		$dec = min( max( 0, $dec + $dec * $percent ), 255 );
		$new_hex .= str_pad( dechex( $dec ) , 2, 0, STR_PAD_LEFT );
	}

	return $new_hex;
}

/**
 * Returns a translated string of the sequence type
 *
 * @param $text
 *
 * @return string|void
 * @since   1.1.0
 */
function sequence_type($text) {
	switch ($text) {
		case 'oneoff':
			return __('One-off', 'kudos-donations');
		case 'first':
			return __('Recurring (first payment)', 'kudos-donations');
		default:
			return __('Recurring', 'kudos-donations');
	}
}