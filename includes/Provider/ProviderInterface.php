<?php
/**
 * ProviderInterface
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

namespace IseardMedia\Kudos\Provider;

use IseardMedia\Kudos\Notice\Notice;

interface ProviderInterface {
	/**
	 * Returns the provider name.
	 */
	public static function get_name(): string;

	/**
	 * Returns the provider's unique slug.
	 */
	public static function get_slug(): string;

	/**
	 * Initialise the provider's hooks and configuration.
	 */
	public function init(): void;

	/**
	 * Returns the provider's state-derived notices. Called for the active provider only, at
	 * notice-collection time.
	 *
	 * @return Notice[]
	 */
	public function get_status_notices(): array;

	/**
	 * Return true if provider is enabled.
	 */
	public static function is_enabled(): bool;

	/**
	 * Returns an svg logo.
	 */
	public static function get_icon_svg(): string;
}
