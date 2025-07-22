<?php
/**
 * Trait for providing custom data sanitization methods.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Repository;

trait SanitizeTrait {

	/**
	 * Handles sanitizing float values.
	 *
	 * @param mixed $value The value to sanitize.
	 */
	public static function sanitize_float( $value ): ?float {
		return \is_scalar( $value ) ? \floatval( $value ) : null;
	}

	/**
	 * Sanitizes float value only if not null.
	 *
	 * @param mixed $value The value to sanitize.
	 */
	public function sanitize_int_or_null( $value ): ?int {
		return null === $value || '' === $value ? null : absint( $value );
	}


	/**
	 * Sanitize JSON field.
	 *
	 * @param mixed $value The field value to sanitize.
	 * @return false|string|null
	 */
	public function sanitize_json_field( $value ) {
		if ( \is_array( $value ) || \is_object( $value ) ) {
			return wp_json_encode( $value );
		}

		if ( \is_string( $value ) && $this->is_valid_json( $value ) ) {
			json_decode( $value );
			return json_last_error() === JSON_ERROR_NONE ? $value : null;
		}

		return null;
	}

	/**
	 * Check if a string contains valid JSON.
	 *
	 * @param string $json The string to check.
	 */
	public static function is_valid_json( string $json ): bool {
		json_decode( $json );
		return json_last_error() === JSON_ERROR_NONE;
	}
}
