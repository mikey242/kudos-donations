<?php
/**
 * VendorInterface
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

namespace IseardMedia\Kudos\Vendor;

interface VendorInterface {
	/**
	 * Returns the provider name.
	 */
	public static function get_name(): string;

	/**
	 * Returns the vendor's unique slug.
	 */
	public static function get_slug(): string;
}
