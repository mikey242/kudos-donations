<?php
/**
 * Subscription Schema.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Schema;

use IseardMedia\Kudos\Enum\FieldType;

class SubscriptionSchema extends BaseSchema {

	/**
	 * {@inheritDoc}
	 */
	public function get_additional_column_schema(): array {
		return [
			'value'                  => $this->make_schema_field( FieldType::FLOAT, [ $this, 'sanitize_float' ] ),
			'currency'               => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'frequency'              => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'years'                  => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'status'                 => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'donor_id'               => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'campaign_id'            => $this->make_schema_field( FieldType::INTEGER, 'absint' ),
			'vendor_customer_id'     => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'vendor_subscription_id' => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
		];
	}
}
