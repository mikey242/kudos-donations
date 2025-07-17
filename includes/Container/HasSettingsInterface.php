<?php
/**
 * Interface for specifying that the target class has settings to be registered.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Container;

interface HasSettingsInterface {

	/**
	 * Gets the settings array.
	 */
	public static function get_settings(): array;
}
