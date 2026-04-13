<?php
/**
 * Localization service.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Service;

/**
 * Collects localization data for each context and exposes it via get_admin() / get_front().
 * Each getter applies a filter at the end so addons can still extend window.kudos.
 */
class LocalizationService {

	public const FILTER_ADMIN = 'kudos_admin_localization';
	public const FILTER_FRONT = 'kudos_front_localization';

	private array $global = [];
	private array $admin  = [];
	private array $front  = [];

	/**
	 * Add a value available in all contexts.
	 *
	 * @param string $key   The key used in window.kudos.
	 * @param mixed  $value The value to expose.
	 */
	public function add_global( string $key, $value ): void {
		$this->global[ $key ] = $value;
	}

	/**
	 * Add a value available only on admin pages.
	 *
	 * @param string $key   The key used in window.kudos.
	 * @param mixed  $value The value to expose.
	 */
	public function add_admin( string $key, $value ): void {
		$this->admin[ $key ] = $value;
	}

	/**
	 * Add a value available only on front-end pages.
	 *
	 * @param string $key   The key used in window.kudos.
	 * @param mixed  $value The value to expose.
	 */
	public function add_front( string $key, $value ): void {
		$this->front[ $key ] = $value;
	}

	/**
	 * Returns the merged global + admin data, then passes it through a filter
	 * so addons can extend window.kudos on admin pages.
	 */
	public function get_admin(): array {
		return (array) apply_filters(
			self::FILTER_ADMIN,
			array_merge( $this->global, $this->admin )
		);
	}

	/**
	 * Returns the merged global + front data, then passes it through a filter
	 * so addons can extend window.kudos on front-end pages.
	 */
	public function get_front(): array {
		return (array) apply_filters(
			self::FILTER_FRONT,
			array_merge( $this->global, $this->front )
		);
	}
}
