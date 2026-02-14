<?php
/**
 * Base Schema.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Schema;

use IseardMedia\Kudos\Domain\Repository\SanitizeTrait;
use IseardMedia\Kudos\Enum\FieldType;

abstract class BaseSchema {

	use SanitizeTrait;

	/**
	 * Defines the common entity schema.
	 */
	private function get_base_column_schema(): array {
		return [
			'id'         => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'wp_post_id' => $this->make_schema_field( FieldType::INTEGER, 'sanitize_text_field' ),
			'title'      => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'created_at' => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'updated_at' => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
		];
	}

	/**
	 * Returns the entire column schema.
	 */
	public function get_column_schema(): array {
		return array_merge( $this->get_base_column_schema(), $this->get_additional_column_schema() );
	}

	/**
	 * Get the schema for the repository.
	 */
	abstract public function get_additional_column_schema(): array;

	/**
	 * Cast the provided row as the types specified in get_column_schema.
	 *
	 * @param array $row The row to cast.
	 */
	public function cast_types( array $row ): array {
		$schema = $this->get_column_schema();

		foreach ( $schema as $key => $args ) {
			if ( ! \array_key_exists( $key, $row ) ) {
				continue;
			}

			$value = $row[ $key ];
			$type  = $args['type'] ?? null;

			switch ( $type ) {
				case FieldType::INTEGER:
					$row[ $key ] = is_numeric( $value ) ? (int) $value : null;
					break;

				case FieldType::FLOAT:
					$row[ $key ] = '' === $value || null === $value ? null : (float) $value;
					break;

				case FieldType::BOOLEAN:
					$row[ $key ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
					break;

				case FieldType::OBJECT:
					if ( \is_array( $value ) || \is_object( $value ) ) {
						// Already decoded.
						$row[ $key ] = $value;
					} elseif ( \is_string( $value ) && $this->is_valid_json( $value ) ) {
						$row[ $key ] = json_decode( $value, true );
					} else {
						$row[ $key ] = null;
					}
					break;

				case FieldType::STRING:
				default:
					$row[ $key ] = \is_scalar( $value ) ? (string) $value : '';
					break;
			}
		}

		return $row;
	}

	/**
	 * Prepares values for insertion into db.
	 *
	 * @param array $data The data for insertion.
	 */
	public function sanitize_data_from_schema( array $data ): array {
		$schema  = $this->get_column_schema();
		$allowed = array_intersect_key( $data, $schema );

		foreach ( $allowed as $key => &$value ) {
			if ( '' === $value ) {
				$value = null;
			}

			$callback = $schema[ $key ]['sanitize_callback'] ?? null;
			if ( $callback && null !== $value && \is_callable( $callback ) ) {
				$value = \call_user_func( $callback, $value );
			}
		}

		return $allowed;
	}

	/**
	 * Returns the schema field.
	 *
	 * @param string   $type The type of field (e.g. string).
	 * @param callable $sanitize Sanitize callback.
	 */
	protected function make_schema_field( string $type, callable $sanitize ): array {
		return [
			'type'              => $type,
			'sanitize_callback' => $sanitize,
		];
	}
}
