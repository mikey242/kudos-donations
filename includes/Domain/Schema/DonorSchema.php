<?php
/**
 * Donor Schema.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Schema;

use IseardMedia\Kudos\Enum\FieldType;

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
			'country'            => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'locale'             => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'vendor_customer_id' => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
		];
	}
}
