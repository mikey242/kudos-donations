<?php

namespace Kudos\Helpers;

use Kudos\Service\TwigService;

class Utils {

	/**
	 * Gets url Mollie will use to return customer to after payment complete.
	 *
	 * @return string|void
	 * @since   1.0.0
	 */
	public static function get_return_url(): string {

		$use_custom = get_option( '_kudos_custom_return_enable' );
		$custom_url = esc_url( get_option( '_kudos_custom_return_url' ) );

		if ( $use_custom && $custom_url ) {
			return $custom_url;
		} else {
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			return home_url( $request_uri );
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
	 * @param string $url e.g style.css.
	 *
	 * @return array
	 * @since   1.0.0
	 */
	private static function get_asset_manifest( string $url ): array {
		$map      = $url . 'dist/assets-manifest.json';
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
		if ( array_key_exists( $asset, $hash ) ) {
			return $url . 'dist/' . $hash[ $asset ];
		}

		return $asset;
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
		if ( array_key_exists( $asset, $hash ) ) {
			return KUDOS_PLUGIN_DIR . '/dist/' . $hash[ $asset ];
		}

		return $asset;
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
	 * @since   1.0.0
	 */
	public static function get_asset_content( string $asset ): string {

		$map      = KUDOS_PLUGIN_URL . 'dist/assets-manifest.json';
		$request  = wp_remote_get( $map );
		$response = wp_remote_retrieve_body( $request );
		$hash     = ! empty( $response ) ? json_decode( $response, true ) : [];

		if ( array_key_exists( $asset, $hash ) ) {
			$asset_request = wp_remote_get( KUDOS_PLUGIN_URL . 'dist/' . $hash[ $asset ] );

			return wp_remote_retrieve_body( $asset_request );
		}

		return $asset;
	}

	/**
	 * Converts three letter currency code into a symbol
	 *
	 * @param string $currency Three letter currency code (EUR, GBP, USD).
	 *
	 * @return string
	 * @since      1.0.2
	 */
	public static function get_currency_symbol( string $currency ): string {

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
	 * @param string $hex Colour as hexadecimal (with or without hash).
	 * @param float $percent Percentage to modify the luminance by.
	 *
	 * @return string Lightened/Darkened colour as hexadecimal (with hash);
	 * @percent float $percent Decimal ( 0.2 = lighten by 20%(), -0.4 = darken by 40%() )
	 * @sice    1.0.2
	 */
	public static function color_luminance( string $hex, float $percent ): string {

		// Remove leading '#' if present.
		$hex = ltrim( $hex, '#' );

		// Expand to 6 character hex code (e.g. FFF -> FFFFFF).
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
		}

		// Convert to decimal.
		$hex = array_map( 'hexdec', str_split( $hex, 2 ) );

		// Change luminosity of decimal colour.
		foreach ( $hex as & $color ) {
			$adjustable_limit = $percent < 0 ? $color : 255 - $color;
			$adjust_amount    = ceil( $adjustable_limit * $percent );

			$color = str_pad( dechex( $color + $adjust_amount ), 2, '0', STR_PAD_LEFT );
		}

		return '#' . implode( $hex );
	}

	/**
	 * Returns a translated string of the sequence type
	 *
	 * @param string $text Mollie sequence type code.
	 *
	 * @return string|void
	 * @since   2.0.0
	 */
	public static function get_sequence_type( string $text ): string {

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
	 * @param string $frequency Mollie frequency code.
	 *
	 * @return string|void
	 * @since   2.0.0
	 */
	public static function get_frequency_name( string $frequency ): string {

		switch ( $frequency ) {
			case '12 months':
				return __( 'Yearly', 'kudos-donations' );
			case '1 month':
				return __( 'Monthly', 'kudos-donations' );
			case '3 months':
				return __( 'Quarterly', 'kudos-donations' );
			case 'oneoff':
				return __( 'One-off', 'kudos-donations' );
			default:
				return $frequency;
		}

	}

	/**
	 * Calculate how many years a subscription is running for
	 * This is based on the number of payments and the frequency.
	 *
	 * @param int $years Number of years as an integer.
	 * @param string $frequency Frequency.
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
	 * @param string|null $prefix Prefix for id.
	 * @param int $length Return value length (minus prefix).
	 *
	 * @return string
	 * @since   2.0.0
	 */
	public static function generate_id( $prefix = null, $length = 10 ): string {

		return $prefix . substr( base_convert( sha1( uniqid( wp_rand() ) ), 16, 36 ), 0, $length );

	}

	/**
	 * Schedules an action using action scheduler
	 *
	 * @param int $timestamp Timestamp of when to run the action.
	 * @param string $hook The name of the WordPress action that is being registered.
	 * @param array $args An array of arguments to pass.
	 * @param bool $overwrite Whether to replace existing scheduled action or not.
	 *
	 * @since    1.0.0
	 */
	public static function schedule_action( int $timestamp, string $hook, array $args = [], bool $overwrite = false ) {

		if ( class_exists( 'ActionScheduler' ) ) {

			if ( $overwrite ) {
				as_unschedule_action( $hook, $args );
			}

			if ( false === as_next_scheduled_action( $hook, $args ) ) {
				as_schedule_single_action( $timestamp, $hook, $args );
			}
		} else {
			do_action( $hook, $args );
		}

	}

	/**
	 * Schedules a recurring action using action scheduler
	 *
	 * @param int $timestamp Timestamp of when to run the action.
	 * @param int $interval How long to wait between runs.
	 * @param string $hook The name of the WordPress action that is being registered.
	 * @param array $args An array of arguments to pass.
	 * @param bool $overwrite Whether to replace existing scheduled action or not.
	 *
	 * @since    1.0.0
	 */
	public static function schedule_recurring_action(
		int $timestamp,
		int $interval,
		string $hook,
		array $args = [],
		bool $overwrite = false
	) {

		if ( class_exists( 'ActionScheduler' ) ) {

			if ( $overwrite ) {
				as_unschedule_action( $hook, $args );
			}

			if ( false === as_next_scheduled_action( $hook, $args ) ) {

				as_schedule_recurring_action( $timestamp, $interval, $hook, $args );
			}
		} else {
			do_action( $hook, $args );
		}

	}

	/**
	 * Returns human readable filesize from given bytes.
	 *
	 * @param int $bytes Size of file in bytes. Usually the value returned from filesize().
	 * @param int $decimals Number of decimal places to return.
	 *
	 * @return string
	 * @link https://www.php.net/manual/en/function.filesize.php
	 * @since 2.4.6
	 */
	public static function human_filesize( int $bytes, int $decimals = 2 ): string {
		$sz     = 'BKMGTP';
		$factor = floor( ( strlen( $bytes ) - 1 ) / 3 );

		return sprintf( "%.{$decimals}f", $bytes / pow( 1024, $factor ) ) . @$sz[ $factor ];
	}

	/**
	 * Uses regex that accepts any word character or hyphen in last name.
	 *
	 * @param $name
	 * @source https://stackoverflow.com/questions/13637145/split-text-string-into-first-and-last-name-in-php
	 *
	 * @return array
	 */
	public static function split_name( $name ): array {
		$name       = trim( $name );
		$last_name  = ( strpos( $name, ' ' ) === false ) ? '' : preg_replace( '#.*\s([\w-]*)$#', '$1', $name );
		$first_name = trim( preg_replace( '#' . preg_quote( $last_name, '#' ) . '#', '', $name ) );

		return array( $first_name, $last_name );
	}

	/**
	 * Returns the Kudos logo SVG markup.
	 *
	 * @param string|null $color
	 * @param int $width
	 *
	 * @return string|null
	 */
	public static function get_kudos_logo_markup( string $color = null, int $width = 24 ): ?string {

		$twig = new TwigService();

		if ( $color ) {
			$lineColor  = $color;
			$heartColor = $color;
		} else {
			$lineColor  = '#2ec4b6';
			$heartColor = '#ff9f1c';
		}

		return apply_filters( 'kudos_get_kudos_logo',
			$twig->render( 'public/logo.html.twig',
				[
					'width'      => $width,
					'lineColor'  => $lineColor,
					'heartColor' => $heartColor,
				] ),
			$width );
	}

	/**
	 * Returns the Kudos logo url.
	 *
	 * @param int $height The height of the image to be returned.
	 *
	 * @return string|null
	 */
	public static function get_logo_url( int $height = 24 ): ?string {

		return apply_filters( 'kudos_get_logo_url',
			self::get_data_uri( self::get_asset_url( 'img/logo-colour.svg' ) ),
			$height );

	}

	/**
	 * Returns an image's base64 encoded data URI for use in 'src' attribute.
	 *
	 * @param string $image_url Url of image to be encoded.
	 *
	 * @return string
	 * @link https://www.genieblog.ch/blog/en/2018/how-to-encode-an-svg-for-the-src-attribute-using-php/
	 *
	 */
	public static function get_data_uri( string $image_url ): string {

		// Get the filetype of supplied image URL.
		$filetype = pathinfo( $image_url, PATHINFO_EXTENSION );

		// Get the contents of the file.
		$request  = wp_remote_get( $image_url );
		$response = wp_remote_retrieve_body( $request );

		// Return data URI if there is data.
		if ( $response ) {

			/*
			 * Don't base64 encode svg.
			 * @link https://css-tricks.com/probably-dont-base64-svg/
			 */
			if ( 'svg' === $filetype ) {
				return 'data:image/svg+xml,' . rawurlencode( $response );
			}

			// All other image types get base64 encoded.
			return 'data:image/' . $filetype . ';base64,' . base64_encode( $response );
		}

		// Return url if nothing found.
		return $image_url;
	}

}
