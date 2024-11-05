<?php
/**
 * Interface for the registration of rest fields on an object.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

namespace IseardMedia\Kudos\Domain;

interface HasRestFieldsInterface {

	/**
	 * Get rest fields to register.
	 */
	public function get_rest_fields(): array;
}
