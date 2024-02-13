<?php
/**
 * Abstract Content Type.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain;

/**
 * Register meta trait.
 *
 * @phpstan-import-type PropertiesShape from HasMetaFieldsInterface
 */
trait RegisterMetaTrait {

	/**
	 * Register meta fields if present.
	 *
	 * @param array  $fields The class to use for getting meta fields.
	 * @param string $object_type Type of object (e.g. post or term).
	 * @param string $object_subtype Object subtype, usually a slug (e.g. the custom post slug).
	 */
	protected function register_meta_fields( array $fields, string $object_type, string $object_subtype ): void {

		foreach ( $fields as $field_name => $args ) {
			$this->register_meta_field( $object_type, $object_subtype, $field_name, $args );
		}
	}

	/**
	 * Register the meta fields for this post type.
	 *
	 * @param string $object_type Type of object (e.g. post or term).
	 * @param string $object_subtype Object subtype, usually a slug (e.g. the custom post slug).
	 * @param string $field_name The field name to register.
	 * @param array  $args Arguments to use when registering.
	 */
	private function register_meta_field( string $object_type, string $object_subtype, string $field_name, array $args ): void {
		// Merge with defaults.
		$args = array_merge(
			[
				'object_subtype' => $object_subtype,
				'single'         => true,
				'show_in_rest'   => true,
			],
			$args
		);

		register_meta(
			$object_type,
			$field_name,
			$args
		);
	}
}
