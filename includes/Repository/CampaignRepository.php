<?php
/**
 * Campaign repository.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Repository;

use IseardMedia\Kudos\Enum\FieldType;

class CampaignRepository extends BaseRepository {

	/**
	 * Field constants.
	 */
	public const TABLE_NAME = 'kudos_campaigns';

	/**
	 * Field constants.
	 */
	public const CURRENCY                   = 'currency';
	public const GOAL                       = 'goal';
	public const SHOW_GOAL                  = 'show_goal';
	public const ADDITIONAL_FUNDS           = 'additional_funds';
	public const INITIAL_TITLE              = 'initial_title';
	public const INITIAL_DESCRIPTION        = 'initial_description';
	public const SUBSCRIPTION_TITLE         = 'subscription_title';
	public const SUBSCRIPTION_DESCRIPTION   = 'subscription_description';
	public const ADDRESS_TITLE              = 'address_title';
	public const ADDRESS_DESCRIPTION        = 'address_description';
	public const MESSAGE_TITLE              = 'message_title';
	public const MESSAGE_DESCRIPTION        = 'message_description';
	public const PAYMENT_TITLE              = 'payment_title';
	public const PAYMENT_DESCRIPTION        = 'payment_description';
	public const EMAIL_ENABLED              = 'email_enabled';
	public const EMAIL_REQUIRED             = 'email_required';
	public const NAME_ENABLED               = 'name_enabled';
	public const NAME_REQUIRED              = 'name_required';
	public const ADDRESS_ENABLED            = 'address_enabled';
	public const ADDRESS_REQUIRED           = 'address_required';
	public const MESSAGE_ENABLED            = 'message_enabled';
	public const MESSAGE_REQUIRED           = 'message_required';
	public const AMOUNT_TYPE                = 'amount_type';
	public const FIXED_AMOUNTS              = 'fixed_amounts';
	public const MINIMUM_DONATION           = 'minimum_donation';
	public const MAXIMUM_DONATION           = 'maximum_donation';
	public const DONATION_TYPE              = 'donation_type';
	public const FREQUENCY_OPTIONS          = 'frequency_options';
	public const THEME_COLOR                = 'theme_color';
	public const TERMS_LINK                 = 'terms_link';
	public const PRIVACY_LINK               = 'privacy_link';
	public const SHOW_RETURN_MESSAGE        = 'show_return_message';
	public const USE_CUSTOM_RETURN_URL      = 'use_custom_return_url';
	public const CUSTOM_RETURN_URL          = 'custom_return_url';
	public const RETURN_MESSAGE_TITLE       = 'return_message_title';
	public const RETURN_MESSAGE_TEXT        = 'return_message_text';
	public const CUSTOM_STYLES              = 'custom_styles';
	public const PAYMENT_DESCRIPTION_FORMAT = 'payment_description_format';

	/**
	 * {@inheritDoc}
	 */
	public function get_column_schema(): array {
		return [
			self::ID                         => $this->make_schema_field( FieldType::INTEGER, null, 'absint' ),
			self::POST_ID                    => $this->make_schema_field( FieldType::INTEGER, null, 'absint' ),
			self::TITLE                      => $this->make_schema_field( FieldType::STRING, '', 'sanitize_text_field' ),
			self::CURRENCY                   => $this->make_schema_field( FieldType::STRING, 'EUR', 'sanitize_text_field' ),
			self::GOAL                       => $this->make_schema_field( FieldType::FLOAT, null, 'floatval' ),
			self::SHOW_GOAL                  => $this->make_schema_field( FieldType::BOOLEAN, false, 'rest_sanitize_boolean' ),
			self::ADDITIONAL_FUNDS           => $this->make_schema_field( FieldType::FLOAT, null, 'floatval' ),
			self::AMOUNT_TYPE                => $this->make_schema_field( FieldType::STRING, 'fixed', 'sanitize_text_field' ),
			self::FIXED_AMOUNTS              => $this->make_schema_field( FieldType::OBJECT, [], [ $this, 'sanitize_json_field' ] ),
			self::MINIMUM_DONATION           => $this->make_schema_field( FieldType::FLOAT, null, 'floatval' ),
			self::MAXIMUM_DONATION           => $this->make_schema_field( FieldType::FLOAT, null, 'floatval' ),
			self::DONATION_TYPE              => $this->make_schema_field( FieldType::STRING, 'oneoff', 'sanitize_text_field' ),
			self::FREQUENCY_OPTIONS          => $this->make_schema_field( FieldType::OBJECT, [], [ $this, 'sanitize_json_field' ] ),
			self::EMAIL_ENABLED              => $this->make_schema_field( FieldType::BOOLEAN, true, 'rest_sanitize_boolean' ),
			self::EMAIL_REQUIRED             => $this->make_schema_field( FieldType::BOOLEAN, true, 'rest_sanitize_boolean' ),
			self::NAME_ENABLED               => $this->make_schema_field( FieldType::BOOLEAN, true, 'rest_sanitize_boolean' ),
			self::NAME_REQUIRED              => $this->make_schema_field( FieldType::BOOLEAN, true, 'rest_sanitize_boolean' ),
			self::ADDRESS_ENABLED            => $this->make_schema_field( FieldType::BOOLEAN, false, 'rest_sanitize_boolean' ),
			self::ADDRESS_REQUIRED           => $this->make_schema_field( FieldType::BOOLEAN, false, 'rest_sanitize_boolean' ),
			self::MESSAGE_ENABLED            => $this->make_schema_field( FieldType::BOOLEAN, false, 'rest_sanitize_boolean' ),
			self::MESSAGE_REQUIRED           => $this->make_schema_field( FieldType::BOOLEAN, false, 'rest_sanitize_boolean' ),
			self::THEME_COLOR                => $this->make_schema_field( FieldType::STRING, '#ff9f1c', 'sanitize_hex_color' ),
			self::TERMS_LINK                 => $this->make_schema_field( FieldType::STRING, '', 'esc_url_raw' ),
			self::PRIVACY_LINK               => $this->make_schema_field( FieldType::STRING, '', 'esc_url_raw' ),
			self::SHOW_RETURN_MESSAGE        => $this->make_schema_field( FieldType::BOOLEAN, false, 'rest_sanitize_boolean' ),
			self::USE_CUSTOM_RETURN_URL      => $this->make_schema_field( FieldType::BOOLEAN, false, 'rest_sanitize_boolean' ),
			self::CUSTOM_RETURN_URL          => $this->make_schema_field( FieldType::STRING, '', 'esc_url_raw' ),
			self::PAYMENT_DESCRIPTION_FORMAT => $this->make_schema_field( FieldType::STRING, '', 'sanitize_text_field' ),
			self::CUSTOM_STYLES              => $this->make_schema_field( FieldType::STRING, '', 'sanitize_textarea_field' ),
			self::INITIAL_TITLE              => $this->make_schema_field( FieldType::STRING, '', 'sanitize_text_field' ),
			self::INITIAL_DESCRIPTION        => $this->make_schema_field( FieldType::STRING, '', 'sanitize_textarea_field' ),
			self::SUBSCRIPTION_TITLE         => $this->make_schema_field( FieldType::STRING, '', 'sanitize_text_field' ),
			self::SUBSCRIPTION_DESCRIPTION   => $this->make_schema_field( FieldType::STRING, '', 'sanitize_textarea_field' ),
			self::ADDRESS_TITLE              => $this->make_schema_field( FieldType::STRING, '', 'sanitize_text_field' ),
			self::ADDRESS_DESCRIPTION        => $this->make_schema_field( FieldType::STRING, '', 'sanitize_textarea_field' ),
			self::MESSAGE_TITLE              => $this->make_schema_field( FieldType::STRING, '', 'sanitize_text_field' ),
			self::MESSAGE_DESCRIPTION        => $this->make_schema_field( FieldType::STRING, '', 'sanitize_textarea_field' ),
			self::PAYMENT_TITLE              => $this->make_schema_field( FieldType::STRING, '', 'sanitize_text_field' ),
			self::PAYMENT_DESCRIPTION        => $this->make_schema_field( FieldType::STRING, '', 'sanitize_textarea_field' ),
			self::RETURN_MESSAGE_TITLE       => $this->make_schema_field( FieldType::STRING, '', 'sanitize_text_field' ),
			self::RETURN_MESSAGE_TEXT        => $this->make_schema_field( FieldType::STRING, '', 'sanitize_textarea_field' ),
			self::CREATED_AT                 => $this->make_schema_field( FieldType::STRING, '', 'sanitize_text_field' ),
			self::UPDATED_AT                 => $this->make_schema_field( FieldType::STRING, '', 'sanitize_text_field' ),
		];
	}


	/**
	 * Get all transactions linked to a specific campaign.
	 *
	 * @param int   $campaign_id The ID of the campaign in the custom table.
	 * @param array $filters     Optional filters (e.g., ['status' => 'paid']).
	 * @return array List of matching transactions.
	 *
	 *  phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
	 */
	public function get_transactions( int $campaign_id, array $filters = [] ): array {
		$transaction_table = $this->wpdb->table( 'kudos_transactions' );

		$criteria = array_merge( [ 'campaign_id' => $campaign_id ], $filters );
		$criteria = array_filter(
			$criteria,
			fn( $key ) => preg_match( '/^[a-zA-Z0-9_]+$/', $key ),
			ARRAY_FILTER_USE_KEY
		);

		$where = $this->build_where_clause( $criteria );

		$sql = "SELECT * FROM {$transaction_table} {$where['sql']} ORDER BY created_at DESC";

		return $this->wpdb->get_results(
			$this->wpdb->prepare( $sql, ...$where['params'] ),
			ARRAY_A
		);
	}
}
