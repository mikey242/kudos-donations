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

use Exception;

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
	 * Generates a unique token based on the post id.
	 *
	 * @throws Exception If post ID invalid.
	 *
	 * @param int $post_id The post id to be hashed.
	 */
	public static function generate_token( int $post_id ): ?string {
		if ( ! is_numeric( $post_id ) || $post_id <= 0 ) {
			throw new Exception( wp_sprintf( 'Invalid post ID supplied to generate_token: %s', (int) $post_id ) );
		}

		return hash_hmac( 'sha256', (string) $post_id, KUDOS_SALT );
	}

	/**
	 * Verifies the provided token against the post id.
	 *
	 * @throws Exception If invalid post_id supplied.
	 *
	 * @param int    $post_id The ID of the post.
	 * @param string $token The token.
	 */
	public static function verify_token( int $post_id, string $token ): bool {
		return hash_equals(
			self::generate_token( $post_id ),
			$token
		);
	}

	/**
	 * Base64 encoded png logo.
	 */
	public static function get_logo(): string {
		// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		return apply_filters(
			'kudos_get_logo',
			base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAACgAAAAgCAYAAABgrToAAAAFVGlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS41LjAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICAgeG1sbnM6ZXhpZj0iaHR0cDovL25zLmFkb2JlLmNvbS9leGlmLzEuMC8iCiAgICB4bWxuczp0aWZmPSJodHRwOi8vbnMuYWRvYmUuY29tL3RpZmYvMS4wLyIKICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIKICAgIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIgogICAgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIKICAgZXhpZjpQaXhlbFhEaW1lbnNpb249IjQwIgogICBleGlmOlBpeGVsWURpbWVuc2lvbj0iMzIiCiAgIGV4aWY6Q29sb3JTcGFjZT0iMSIKICAgdGlmZjpJbWFnZVdpZHRoPSI0MCIKICAgdGlmZjpJbWFnZUxlbmd0aD0iMzIiCiAgIHRpZmY6UmVzb2x1dGlvblVuaXQ9IjIiCiAgIHRpZmY6WFJlc29sdXRpb249IjcyLzEiCiAgIHRpZmY6WVJlc29sdXRpb249IjcyLzEiCiAgIHBob3Rvc2hvcDpDb2xvck1vZGU9IjMiCiAgIHBob3Rvc2hvcDpJQ0NQcm9maWxl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' )
		);
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
	 * Formats value for display. Do not use to send to payment provider or store value.
	 *
	 * @param string $value The value to display.
	 */
	public static function format_value_for_display( string $value ): string {
		return number_format_i18n( \floatval( $value ), 2 );
	}

	/**
	 * Formats value for use with payment provider or storage.
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
