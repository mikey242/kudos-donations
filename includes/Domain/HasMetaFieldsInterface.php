<?php
/**
 * Interface for the registration of meta/custom-fields on an object
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

namespace IseardMedia\Kudos\Domain;

interface HasMetaFieldsInterface {

	/**
	 * Get meta fields to register.
	 */
	public static function get_meta_config(): array;
}
