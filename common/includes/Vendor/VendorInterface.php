<?php
/**
 * VendorInterface
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

namespace IseardMedia\Kudos\Vendor;

interface VendorInterface {
	/**
	 * Returns the provider name.
	 */
	public static function get_name(): string;

	/**
	 * Refresh the cached audiences.
	 */
	public function refresh(): bool;
}
