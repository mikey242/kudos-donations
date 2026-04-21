<?php
/**
 * Localization helper.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Helper;

/**
 * Collects localization data for each context and exposes it for wp_localize_script.
 * Each getter applies a filter so addons can extend window.kudos.
 */
class Localization {

	public const FILTER_GLOBAL = 'kudos_global_localization';
	public const FILTER_ADMIN  = 'kudos_admin_localization';
	public const FILTER_FRONT  = 'kudos_front_localization';

	private static array $global = [];
	private static array $admin  = [];
	private static array $front  = [];

	/**
	 * Add a value available in all contexts.
	 *
	 * @param string $key   The key used in window.kudos.
	 * @param mixed  $value The value to expose.
	 */
	public static function add_global( string $key, $value ): void {
		self::$global[ $key ] = $value;
	}

	/**
	 * Add a value available only on admin pages.
	 *
	 * @param string $key   The key used in window.kudos.
	 * @param mixed  $value The value to expose.
	 */
	public static function add_admin( string $key, $value ): void {
		self::$admin[ $key ] = $value;
	}

	/**
	 * Add a value available only on front-end pages.
	 *
	 * @param string $key   The key used in window.kudos.
	 * @param mixed  $value The value to expose.
	 */
	public static function add_front( string $key, $value ): void {
		self::$front[ $key ] = $value;
	}

	/**
	 * Returns global data.
	 * The global data is passed through a filter for addon extensibility.
	 */
	public static function get_global(): array {
		return (array) apply_filters( self::FILTER_GLOBAL, self::$global );
	}

	/**
	 * Returns global data merged with admin-specific data nested under 'admin'.
	 * The admin-specific data is passed through a filter for addon extensibility.
	 */
	public static function get_admin(): array {
		$admin = (array) apply_filters( self::FILTER_ADMIN, self::$admin );
		return array_merge( self::$global, [ 'admin' => $admin ] );
	}

	/**
	 * Returns global data merged with front-specific data nested under 'front'.
	 * The front-specific data is passed through a filter for addon extensibility.
	 */
	public static function get_front(): array {
		$front = (array) apply_filters( self::FILTER_FRONT, self::$front );
		return array_merge( self::$global, [ 'front' => $front ] );
	}

	/**
	 * Resets all localization data. Intended for use in tests.
	 */
	public static function reset(): void {
		self::$global = [];
		self::$admin  = [];
		self::$front  = [];
	}
}
