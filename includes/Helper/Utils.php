<?php
/**
 * Utils.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Helper;

use IseardMedia\Kudos\Domain\Entity\BaseEntity;
use IseardMedia\Kudos\Domain\Table\CampaignsTable;
use IseardMedia\Kudos\Domain\Table\DonorsTable;
use IseardMedia\Kudos\Domain\Table\SubscriptionsTable;
use IseardMedia\Kudos\Domain\Table\TransactionsTable;

class Utils {

	/**
	 * Creates the required tables.
	 *
	 * @param WpDb $wpdb WpDb wrapper instance.
	 */
	public static function create_all_tables( WpDb $wpdb ): void {
		$tables = [
			CampaignsTable::class,
			DonorsTable::class,
			TransactionsTable::class,
			SubscriptionsTable::class,
		];

		foreach ( $tables as $table_class ) {
			( new $table_class( $wpdb ) )->create_table();
		}
	}

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
		if ( ! ( $years > 0 ) ) {
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
	 * @param int    $timestamp Timestamp of when to run the action (e.g strtotime( '+1 minute' )).
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
	 * Enqueues an async action using Action Scheduler.
	 *
	 * @param string      $hook       The name of the WordPress action to trigger.
	 * @param array       $args       Arguments to pass to the action callback.
	 * @param string|null $group      (Optional) Group name for the action.
	 * @param bool        $overwrite  Whether to replace existing async action.
	 */
	public static function enqueue_async_action(
		string $hook,
		array $args = [],
		?string $group = null,
		bool $overwrite = false
	): void {
		if ( class_exists( 'ActionScheduler' ) && \function_exists( 'as_enqueue_async_action' ) ) {
			if ( $overwrite ) {
				as_unschedule_action( $hook, $args, $group );
			}

			// Avoid scheduling duplicates if $overwrite is false.
			if ( $overwrite || false === as_next_scheduled_action( $hook, $args, $group ) ) {
				as_enqueue_async_action( $hook, $args, $group );
			}
		} else {
			// Fallback: run it immediately.
			do_action( $hook, ...array_values( $args ) );
		}
	}

	/**
	 * SVG logo.
	 */
	public static function get_kudos_logo_svg(): string {
		return '<svg class="logo origin-center duration-500 ease-in-out m-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 555 449"><path class="logo-line" fill="#2ec4b6" d="M0,65.107C0,47.839 6.86,31.278 19.07,19.067C31.281,6.857 47.842,-0.003 65.11,-0.003L65.112,-0.003C101.202,-0.003 130.458,29.253 130.458,65.343L130.458,383.056C130.458,400.374 123.579,416.982 111.333,429.227C99.088,441.473 82.48,448.352 65.162,448.352L65.161,448.352C29.174,448.352 0.001,419.179 0.001,383.192C0.001,298.138 0,150.136 0,65.107Z"></path><path class="logo-heart origin-center duration-500 ease-in-out" fill="#ff9f1c" d="M489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"></path></svg>';
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
	 * @param float $value The value to format.
	 */
	public static function format_value_for_use( float $value ): string {
		return number_format( $value, 2, '.', '' );
	}

	/**
	 * Returns the company name.
	 */
	public static function get_company_name(): string {
		return apply_filters( 'kudos_invoice_company_name', get_bloginfo( 'name' ) );
	}

	/**
	 * Returns the company logo as a URL or local file path.
	 *
	 * @param string $type 'url' for an HTTP URL (e.g. for Dompdf), 'path' for a local file path (e.g. for PHPMailer).
	 * @param string $size WordPress image size name passed as a hint to the filter.
	 */
	public static function get_company_logo( string $type = 'url', string $size = 'medium' ): string {
		$image = Assets::get_image( 'logo-colour.png' );
		$logo  = $image ? $image[ $type ] : '';

		return (string) apply_filters( 'kudos_company_logo', $logo, $size, $type );
	}

	/**
	 * Returns the company URL.
	 */
	public static function get_company_url(): string {
		return (string) apply_filters( 'kudos_company_url', 'https://kudosdonations.com' );
	}

	/**
	 * Returns a formatted id based on the entity id and created date.
	 *
	 * @param BaseEntity $entity Entity object.
	 * @param string     $singular_name The entity singular name (e.g. Transaction).
	 */
	public static function get_id( BaseEntity $entity, string $singular_name ): string {
		$id   = $entity->id;
		$date = $entity->created_at;
		$year = substr( $date, 0, 4 );
		$type = substr( strtolower( $singular_name ), 0, 2 );
		return 'k' . $type . '_' . $year . $id;
	}

	/**
	 * Switch locale.
	 *
	 * @param string $locale Locale code to switch to.
	 */
	public static function switch_locale( string $locale ): void {
		if ( switch_to_locale( $locale ) ) {

			// Ensure translations are reloaded from WP_LANG_DIR/plugins/.
			unload_textdomain( 'kudos-donations' );
			load_textdomain( 'kudos-donations', WP_LANG_DIR . '/plugins/kudos-donations-' . $locale . '.mo' );
		}
	}

	/**
	 * Normalize a browser locale like 'nl' to a full WP locale like 'nl_NL'.
	 *
	 * @param string $locale Short or full locale code.
	 * @return string Normalized locale or fallback to 'en_US'.
	 */
	public static function normalize_locale( string $locale ): string {
		$locale    = str_replace( '-', '_', strtolower( $locale ) );
		$available = get_available_languages();

		foreach ( $available as $available_locale ) {
			if ( stripos( $available_locale, $locale ) === 0 ) {
				return $available_locale;
			}
		}

		return 'en_US'; // fallback.
	}

	/**
	 * Returns true if current page is a Kudos Donations admin page.
	 */
	public static function is_kudos_admin(): bool {
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return substr( $page, 0, 6 ) === 'kudos-';
	}
}
