<?php
/**
 * Campaign Schema.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Schema;

use IseardMedia\Kudos\Enum\FieldType;

class CampaignSchema extends BaseSchema {

	/**
	 * {@inheritDoc}
	 */
	public function get_additional_column_schema(): array {
		return [
			'wp_post_slug'               => $this->make_schema_field( FieldType::INTEGER, 'sanitize_title_with_dashes' ),
			'currency'                   => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'goal'                       => $this->make_schema_field( FieldType::FLOAT, [ $this, 'sanitize_float' ] ),
			'show_goal'                  => $this->make_schema_field( FieldType::BOOLEAN, 'rest_sanitize_boolean' ),
			'additional_funds'           => $this->make_schema_field( FieldType::FLOAT, [ $this, 'sanitize_float' ] ),
			'amount_type'                => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'fixed_amounts'              => $this->make_schema_field( FieldType::OBJECT, [ $this, 'sanitize_json_field' ] ),
			'minimum_donation'           => $this->make_schema_field( FieldType::FLOAT, [ $this, 'sanitize_float' ] ),
			'maximum_donation'           => $this->make_schema_field( FieldType::FLOAT, [ $this, 'sanitize_float' ] ),
			'donation_type'              => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'frequency_options'          => $this->make_schema_field( FieldType::OBJECT, [ $this, 'sanitize_json_field' ] ),
			'email_enabled'              => $this->make_schema_field( FieldType::BOOLEAN, 'rest_sanitize_boolean' ),
			'email_required'             => $this->make_schema_field( FieldType::BOOLEAN, 'rest_sanitize_boolean' ),
			'name_enabled'               => $this->make_schema_field( FieldType::BOOLEAN, 'rest_sanitize_boolean' ),
			'name_required'              => $this->make_schema_field( FieldType::BOOLEAN, 'rest_sanitize_boolean' ),
			'address_enabled'            => $this->make_schema_field( FieldType::BOOLEAN, 'rest_sanitize_boolean' ),
			'address_required'           => $this->make_schema_field( FieldType::BOOLEAN, 'rest_sanitize_boolean' ),
			'message_enabled'            => $this->make_schema_field( FieldType::BOOLEAN, 'rest_sanitize_boolean' ),
			'message_required'           => $this->make_schema_field( FieldType::BOOLEAN, 'rest_sanitize_boolean' ),
			'theme_color'                => $this->make_schema_field( FieldType::STRING, 'sanitize_hex_color' ),
			'terms_link'                 => $this->make_schema_field( FieldType::STRING, 'esc_url_raw' ),
			'privacy_link'               => $this->make_schema_field( FieldType::STRING, 'esc_url_raw' ),
			'show_return_message'        => $this->make_schema_field( FieldType::BOOLEAN, 'rest_sanitize_boolean' ),
			'use_custom_return_url'      => $this->make_schema_field( FieldType::BOOLEAN, 'rest_sanitize_boolean' ),
			'custom_return_url'          => $this->make_schema_field( FieldType::STRING, 'esc_url_raw' ),
			'payment_description_format' => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'custom_styles'              => $this->make_schema_field( FieldType::STRING, 'sanitize_textarea_field' ),
			'initial_title'              => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'initial_description'        => $this->make_schema_field( FieldType::STRING, 'sanitize_textarea_field' ),
			'subscription_title'         => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'subscription_description'   => $this->make_schema_field( FieldType::STRING, 'sanitize_textarea_field' ),
			'address_title'              => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'address_description'        => $this->make_schema_field( FieldType::STRING, 'sanitize_textarea_field' ),
			'message_title'              => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'message_description'        => $this->make_schema_field( FieldType::STRING, 'sanitize_textarea_field' ),
			'payment_title'              => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'payment_description'        => $this->make_schema_field( FieldType::STRING, 'sanitize_textarea_field' ),
			'return_message_title'       => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'return_message_text'        => $this->make_schema_field( FieldType::STRING, 'sanitize_textarea_field' ),
		];
	}
}
