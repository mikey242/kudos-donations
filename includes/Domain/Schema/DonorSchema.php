<?php
/**
 * Donor Schema.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Schema;

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Country;

class DonorSchema extends BaseSchema {

	/**
	 * {@inheritDoc}
	 */
	public function get_additional_column_schema(): array {
		return [
			'email'              => $this->make_schema_field( FieldType::STRING, 'sanitize_email' ),
			'mode'               => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'name'               => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'business_name'      => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'street'             => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'postcode'           => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'city'               => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'country'            => $this->make_schema_field( FieldType::STRING, [ $this, 'sanitize_country' ] ),
			'locale'             => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'vendor_customer_id' => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
		];
	}

	/**
	 * Sanitise country input.
	 *
	 * Accepts ISO 3166-1 alpha-2 codes or full country names.
	 * Full names are converted to alpha-2 codes where possible.
	 *
	 * @param string $value The country value to sanitise.
	 */
	public function sanitize_country( string $value ): string {
		$value = sanitize_text_field( $value );

		// Already a 2-letter code.
		if ( preg_match( '/^[A-Z]{2}$/i', $value ) ) {
			return strtoupper( $value );
		}

		// Attempt to convert full name to ISO code.
		return Country::get_iso_code_by_name( $value ) ?? '';
	}
}
