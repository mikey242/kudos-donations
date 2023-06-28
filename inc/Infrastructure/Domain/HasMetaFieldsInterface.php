<?php
/**
 * Interface for the registration of meta/custom-fields on a PostType
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

namespace IseardMedia\Kudos\Infrastructure\Domain;

/**
 * Interface for declaring an entity has fields to register.
 */
interface HasMetaFieldsInterface {

	/**
	 * Get meta fields to register.
	 *
	 * @return array
	 */
	public function get_meta_fields(): array;

}
