<?php
/**
 * Utils.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Helper;

class Utils {

	/**
	 * Converts three letter currency code into a symbol.
	 *
	 * @param string $currency Three letter currency code (EUR, GBP, USD).
	 */
	public static function get_currency_symbol( string $currency ): string {
		$currency = strtoupper( $currency );

		switch ( $currency ) {
			case 'EUR':
				$result = '&#8364;';
				break;
			case 'USD':
				$result = '&#36;';
				break;
			case 'GBP':
				$result = '&#163;';
				break;
			case 'JPY':
				$result = '&#165;';
				break;
			default:
				$result = '&#8364;';
		}

		return $result;
	}

	/**
	 * Calculate how many years a subscription is running for.
	 * This is based on the number of payments and the frequency.
	 *
	 * @param int    $years Number of years as an integer.
	 * @param string $frequency Frequency.
	 * @return float|int|null
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
		return $prefix . substr( base_convert( sha1( uniqid( (string) wp_rand() ) ), 16, 36 ), 0, $length );
	}

	/**
	 * Schedules an action using action scheduler.
	 *
	 * @param int    $timestamp Timestamp of when to run the action.
	 * @param string $hook The name of the WordPress action that is being registered.
	 * @param array  $args An array of arguments to pass.
	 * @param bool   $overwrite Whether to replace existing scheduled action or not.
	 */
	public static function schedule_action( int $timestamp, string $hook, array $args = [], bool $overwrite = false ): void {
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
}
