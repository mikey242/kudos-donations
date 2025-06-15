<?php
/**
 * Register rest fields.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain;

trait RegisterRestFieldsTrait {

	/**
	 * Register meta fields if present.
	 *
	 * @param array  $fields The class to use for getting meta fields.
	 * @param string $object_type Type of object (e.g. post or term).
	 */
	protected function register_rest_fields( array $fields, string $object_type ): void {

		foreach ( $fields as $field_name => $args ) {
			$this->register_rest_field( $object_type, $field_name, $args );
		}
	}

	/**
	 * Register the meta fields for this post type.
	 *
	 * @param string $object_type Type of object (e.g. post or term).
	 * @param string $field_name The field name to register.
	 * @param array  $args Arguments to use when registering.
	 */
	private function register_rest_field( string $object_type, string $field_name, array $args ): void {

		add_action(
			'rest_api_init',
			function () use ( $object_type, $field_name, $args ): void {
				register_rest_field( $object_type, $field_name, $args );
			}
		);
	}
}
