<?php
/**
 * Generic helper utilities.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

namespace Kudos\Helpers;

class Utils {

	/**
	 * Gets url Mollie will use to return customer to after payment complete.
	 */
	public static function get_return_url(): string {

		$use_custom = get_option( '_kudos_completed_payment' ) === 'url';
		$custom_url = esc_url( get_option( '_kudos_custom_return_url' ) );

		if ( $use_custom && $custom_url ) {
			return $custom_url;
		} else {
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			return home_url( $request_uri );
		}
	}

	/**
	 * Converts three letter currency code into a symbol.
	 *
	 * @param string $currency Three letter currency code (EUR, GBP, USD).
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
	 * @param string $hex Colour as hexadecimal (with or without hash).
	 * @param float  $percent Percentage to modify the luminance by.
	 * @return string Lightened/Darkened colour as hexadecimal (with hash);
	 *
	 * @source https://gist.github.com/stephenharris/5532899
	 * @percent float $percent Decimal ( 0.2 = lighten by 20%(), -0.4 = darken by 40%() )
	 */
	public static function color_luminance( string $hex, float $percent ): string {

		// Remove leading '#' if present.
		$hex = ltrim( $hex, '#' );

		// Expand to 6 character hex code (e.g. FFF -> FFFFFF).
		if ( \strlen( $hex ) === 3 ) {
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
	 * Returns a translated string of the sequence type.
	 *
	 * @param string $text Mollie sequence type code.
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
	 * Returns subscription frequency name based on number of months.
	 *
	 * @param string $frequency Mollie frequency code.
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
	 * Calculate how many years a subscription is running for.
	 * This is based on the number of payments and the frequency.
	 *
	 * @param int    $years Number of years as an integer.
	 * @param string $frequency Frequency.
	 * @return int|null
	 */
	public static function get_times_from_years( int $years, string $frequency ) {

		if ( ! $years > 0 ) {
			return null;
		}

		return ( 12 / \intval( $frequency ) ) * $years - 1;
	}

	/**
	 * Generate a random and unique ID with specified prefix.
	 *
	 * @param string|null $prefix Prefix for id.
	 * @param int         $length Return value length (minus prefix).
	 */
	public static function generate_id( ?string $prefix = null, int $length = 10 ): string {

		return $prefix . substr( base_convert( sha1( uniqid( wp_rand() ) ), 16, 36 ), 0, $length );
	}

	/**
	 * Schedules an action using action scheduler.
	 *
	 * @param int    $timestamp Timestamp of when to run the action.
	 * @param string $hook The name of the WordPress action that is being registered.
	 * @param array  $args An array of arguments to pass.
	 * @param bool   $overwrite Whether to replace existing scheduled action or not.
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
	 * Schedules a recurring action using action scheduler.
	 *
	 * @param int    $timestamp Timestamp of when to run the action.
	 * @param int    $interval How long to wait between runs.
	 * @param string $hook The name of the WordPress action that is being registered.
	 * @param array  $args An array of arguments to pass.
	 * @param bool   $overwrite Whether to replace existing scheduled action or not.
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
	 * Truncates string at specified length and return with ellipsis
	 * if longer.
	 *
	 * @param string $text The string to truncate.
	 * @param int    $length The length to truncate at.
	 */
	public static function truncate_string( string $text, int $length ): string {

		return \strlen( $text ) > $length ? substr( $text, 0, $length ) . '...' : $text;
	}

	/**
	 * Generates a unique token based on the post id.
	 *
	 * @param string $message The post id to be hashed.
	 */
	public static function generate_token( string $message ): ?string {
		return hash_hmac( 'sha256', $message, KUDOS_SALT );
	}

	/**
	 * Verifies the provided token against the post id.
	 *
	 * @param string $message The ID of the post.
	 * @param string $token The token.
	 */
	public static function verify_token( string $message, string $token ): bool {
		return hash_equals(
			self::generate_token( $message ),
			$token
		);
	}
}
