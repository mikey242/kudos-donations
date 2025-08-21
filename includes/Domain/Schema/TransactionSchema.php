<?php
/**
 * Transaction Schema.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Schema;

use IseardMedia\Kudos\Enum\FieldType;

class TransactionSchema extends BaseSchema {

	/**
	 * {@inheritDoc}
	 */
	public function get_additional_column_schema(): array {
		return [
			'value'              => $this->make_schema_field( FieldType::FLOAT, [ $this, 'sanitize_float' ] ),
			'currency'           => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'status'             => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'method'             => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'mode'               => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'sequence_type'      => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'donor_id'           => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'campaign_id'        => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'subscription_id'    => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'refunds'            => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'message'            => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'vendor'             => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'invoice_number'     => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'checkout_url'       => $this->make_schema_field( FieldType::STRING, 'sanitize_url' ),
			'vendor_customer_id' => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'vendor_payment_id'  => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
		];
	}
}
