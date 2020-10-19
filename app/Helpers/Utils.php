<?php

namespace Kudos\Helpers;

class Utils {

	/**
	 * Gets url Mollie will use to return customer to after payment complete
	 *
	 * @return string|void
	 * @since   1.0.0
	 */
	public static function get_return_url() {

		$use_custom = get_option( '_kudos_custom_return_enable' );
		$custom_url = esc_url( get_option( '_kudos_custom_return_url' ) );

		if ( $use_custom && $custom_url ) {
			return $custom_url;
		} else {
			$returnUrl = is_ssl() ? 'https://' : 'http://';
			$returnUrl .= $_SERVER['HTTP_HOST'] . parse_url( $_SERVER["REQUEST_URI"], PHP_URL_PATH );

			return $returnUrl;
		}

	}

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
	 * @since   1.0.0
	 */
	public static function get_asset_url( string $asset, bool $path = false ) {

		$map  = KUDOS_PLUGIN_DIR . '/dist/manifest.json';
		$hash = file_exists( $map ) ? json_decode( file_get_contents( $map ), true ) : [];

		if ( array_key_exists( $asset, $hash ) ) {
			if ( ! $path ) {
				return KUDOS_PLUGIN_URL . 'dist/' . $hash[ $asset ];
			}

			return KUDOS_PLUGIN_DIR . '/dist/' . $hash[ $asset ];
		}

		return $asset;
	}

	/**
	 * Converts three letter currency code into a symbol
	 *
	 * @param string $currency
	 *
	 * @return string
	 * @since      1.0.2
	 */
	public static function get_currency_symbol( string $currency ) {

		$currency = strtoupper( $currency );

		switch ( $currency ) {
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
	 * Lightens/darkens a given colour (hex format), returning the altered colour in hex format.
	 *
	 * @source https://gist.github.com/stephenharris/5532899
	 * @param string $hex Colour as hexadecimal (with or without hash);
	 * @param float $percent
	 *
	 * @return string Lightened/Darkened colour as hexadecimal (with hash);
	 * @percent float $percent Decimal ( 0.2 = lighten by 20%(), -0.4 = darken by 40%() )
	 * @sice    1.0.2
	 */
	public static function color_luminance( string $hex, float $percent ) {

		// Remove leading '#' if present
		$hex = ltrim( $hex, '#' );

		// Expand to 6 character hex code (e.g. FFF -> FFFFFF)
		if ( strlen( $hex ) == 3 ) {
			$hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
		}

		// Convert to decimal
		$hex = array_map( 'hexdec', str_split( $hex, 2 ) );

		// Change luminosity of decimal colour
		foreach ( $hex as & $color ) {
			$adjustableLimit = $percent < 0 ? $color : 255 - $color;
			$adjustAmount    = ceil( $adjustableLimit * $percent );

			$color = str_pad( dechex( $color + $adjustAmount ), 2, '0', STR_PAD_LEFT );
		}

		return '#' . implode( $hex );
	}

	/**
	 * Returns a translated string of the sequence type
	 *
	 * @param $text
	 *
	 * @return string|void
	 * @since   2.0.0
	 */
	public static function get_sequence_type( $text ) {

		switch ( $text ) {
			case 'oneoff':
				return __( 'One-off', 'kudos-donations' );
			case 'first':
				return __( 'Recurring (first payment)', 'kudos-donations' );
			default:
				return __( 'Recurring', 'kudos-donations' );
		}

	}

	/**
	 * Returns subscription frequency name based on number of months
	 *
	 * @param string $frequency
	 *
	 * @return string|void
	 * @since   2.0.0
	 */
	public static function get_frequency_name( string $frequency ) {

		switch ( $frequency ) {
			case '12 months':
				return __( 'Yearly', 'kudos-donations' );
			case '1 month':
				return __( 'Monthly', 'kudos-donations' );
			case '3 months':
				return __( 'Quarterly', 'kudos-donations' );
			case "oneoff":
				return __( 'One-off', 'kudos-donations' );
			default:
				return $frequency;
		}

	}

	/**
	 * Calculate how many years a subscription is running for
	 * This is based on the number of payments and the frequency.
	 *
	 * @param int $years
	 * @param string $frequency
	 *
	 * @return int|null
	 * @since   2.0.0
	 */
	public static function get_times_from_years( int $years, string $frequency ) {

		if ( ! $years > 0 ) {
			return null;
		}

		return ( 12 / intval( $frequency ) ) * $years - 1;

	}

	/**
	 * Generate a random and unique ID with specified prefix
	 *
	 * @param string|null $prefix
	 * @param int $length
	 *
	 * @return string
	 * @since   2.0.0
	 */
	public static function generate_id( $prefix = null, $length = 10 ) {

		return $prefix . substr( base_convert( sha1( uniqid( mt_rand() ) ), 16, 36 ), 0, $length );

	}

}