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
	 * Returns an array of supported currencies and their symbols.
	 *
	 * @return string[]
	 */
	public static function get_currencies(): array {
		return apply_filters(
			'kudos_get_currencies',
			[
				'AED' => 'د.إ',
				'AUD' => '$',
				'BGN' => 'лв',
				'BRL' => 'R$',
				'CAD' => '$',
				'CHF' => 'CHF',
				'CZK' => 'Kč',
				'DKK' => 'kr',
				'EUR' => '€',
				'GBP' => '£',
				'HKD' => '$',
				'HUF' => 'Ft',
				'ILS' => '₪',
				'ISK' => 'kr',
				'JPY' => '¥',
				'MXN' => '$',
				'MYR' => 'RM',
				'NOK' => 'kr',
				'NZD' => '$',
				'PHP' => '₱',
				'PLN' => 'zł',
				'RON' => 'lei',
				'RUB' => '₽',
				'SEK' => 'kr',
				'SGD' => '$',
				'THB' => '฿',
				'TWD' => 'NT$',
				'USD' => '$',
				'ZAR' => 'R',
			]
		);
	}

	/**
	 * Calculate how many years a subscription is running for.
	 * This is based on the number of payments and the frequency.
	 *
	 * @param int    $years Number of years as an integer.
	 * @param string $frequency Frequency.
	 */
	public static function get_times_from_years( int $years, string $frequency ): ?int {
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

	/**
	 * SVG logo.
	 */
	public static function get_logo_svg(): string {
		return '<svg class="w-6 h-6 logo origin-center duration-500 ease-in-out m-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 555 449"><path class="logo-line" fill="#2ec4b6" d="M0,65.107C0,47.839 6.86,31.278 19.07,19.067C31.281,6.857 47.842,-0.003 65.11,-0.003L65.112,-0.003C101.202,-0.003 130.458,29.253 130.458,65.343L130.458,383.056C130.458,400.374 123.579,416.982 111.333,429.227C99.088,441.473 82.48,448.352 65.162,448.352L65.161,448.352C29.174,448.352 0.001,419.179 0.001,383.192C0.001,298.138 0,150.136 0,65.107Z"></path><path class="logo-heart origin-center duration-500 ease-in-out" fill="#ff9f1c" d="M489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"></path></svg>';
	}

	/**
	 * Sanitizes float values.
	 *
	 * @param mixed $input The value to sanitize.
	 * @return mixed
	 */
	public static function sanitize_float( $input ) {
		return filter_var( $input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
	}

	/**
	 * Formats donation value for display. Do not use to send to payment provider or store value.
	 *
	 * @param string $value The value to display.
	 */
	public static function format_value_for_display( string $value ): string {
		return number_format_i18n( \floatval( $value ), 2 );
	}

	/**
	 * Formats donation value for use with payment provider or storage. Should not be localized.
	 *
	 * @param string $value The value to format.
	 */
	public static function format_value_for_use( string $value ): string {
		return number_format( \floatval( $value ), 2, '.', '' );
	}

	/**
	 * Returns the company name.
	 */
	public static function get_company_name(): string {
		return apply_filters( 'kudos_invoice_company_name', get_bloginfo( 'name' ) );
	}

	/**
	 * Returns the company logo URI.
	 */
	public static function get_company_logo_svg(): string {
		return apply_filters( 'kudos_company_logo', self::get_logo_svg() );
	}
}
