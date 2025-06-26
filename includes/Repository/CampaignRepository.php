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

	use SanitizeTrait;

	/**
	 * Field constants.
	 */
	public const TABLE_NAME = 'kudos_campaigns';

	/**
	 * Field constants.
	 */
	public const POST_SLUG                  = 'wp_post_slug';
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
	public static function get_singular_name(): string {
		return _x( 'Campaign', 'Campaign post type singular name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_plural_name(): string {
		return _x( 'Campaigns', 'Campaign post type plural name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_additional_column_schema(): array {
		return [
			self::ID                         => $this->make_schema_field( FieldType::INTEGER, null, 'absint' ),
			self::POST_ID                    => $this->make_schema_field( FieldType::INTEGER, null, [ $this, 'sanitize_int_or_null' ] ),
			self::TITLE                      => $this->make_schema_field( FieldType::STRING, '', 'sanitize_text_field' ),
			self::CURRENCY                   => $this->make_schema_field( FieldType::STRING, 'EUR', 'sanitize_text_field' ),
			self::GOAL                       => $this->make_schema_field( FieldType::FLOAT, null, [ $this, 'sanitize_float' ] ),
			self::SHOW_GOAL                  => $this->make_schema_field( FieldType::BOOLEAN, false, 'rest_sanitize_boolean' ),
			self::ADDITIONAL_FUNDS           => $this->make_schema_field( FieldType::FLOAT, null, [ $this, 'sanitize_float' ] ),
			self::AMOUNT_TYPE                => $this->make_schema_field( FieldType::STRING, 'fixed', 'sanitize_text_field' ),
			self::FIXED_AMOUNTS              => $this->make_schema_field( FieldType::OBJECT, [ '5', '10', '25', '50' ], [ $this, 'sanitize_json_field' ] ),
			self::MINIMUM_DONATION           => $this->make_schema_field( FieldType::FLOAT, 1, [ $this, 'sanitize_float' ] ),
			self::MAXIMUM_DONATION           => $this->make_schema_field( FieldType::FLOAT, 5000, [ $this, 'sanitize_float' ] ),
			self::DONATION_TYPE              => $this->make_schema_field( FieldType::STRING, 'oneoff', 'sanitize_text_field' ),
			self::FREQUENCY_OPTIONS          => $this->make_schema_field(
				FieldType::OBJECT,
				[
					'12 months' => __( 'Yearly', 'kudos-donations' ),
					'3 months'  => __( 'Quarterly', 'kudos-donations' ),
					'1 month'   => __( 'Monthly', 'kudos-donations' ),
				],
				[ $this, 'sanitize_json_field' ]
			),
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
			self::PAYMENT_DESCRIPTION_FORMAT => $this->make_schema_field( FieldType::STRING, __( 'Donation ({{campaign_name}}) - {{order_id}}', 'kudos-donations' ), 'sanitize_text_field' ),
			self::CUSTOM_STYLES              => $this->make_schema_field( FieldType::STRING, '', 'sanitize_textarea_field' ),
			self::INITIAL_TITLE              => $this->make_schema_field( FieldType::STRING, __( 'Support us!', 'kudos-donations' ), 'sanitize_text_field' ),
			self::INITIAL_DESCRIPTION        => $this->make_schema_field( FieldType::STRING, __( 'Your support is greatly appreciated and will help to keep us going.', 'kudos-donations' ), 'sanitize_textarea_field' ),
			self::SUBSCRIPTION_TITLE         => $this->make_schema_field( FieldType::STRING, __( 'Subscription', 'kudos-donations' ), 'sanitize_text_field' ),
			self::SUBSCRIPTION_DESCRIPTION   => $this->make_schema_field( FieldType::STRING, __( 'How often would you like to donate?', 'kudos-donations' ), 'sanitize_textarea_field' ),
			self::ADDRESS_TITLE              => $this->make_schema_field( FieldType::STRING, __( 'Address', 'kudos-donations' ), 'sanitize_text_field' ),
			self::ADDRESS_DESCRIPTION        => $this->make_schema_field( FieldType::STRING, __( 'Please fill in your address', 'kudos-donations' ), 'sanitize_textarea_field' ),
			self::MESSAGE_TITLE              => $this->make_schema_field( FieldType::STRING, __( 'Message', 'kudos-donations' ), 'sanitize_text_field' ),
			self::MESSAGE_DESCRIPTION        => $this->make_schema_field( FieldType::STRING, __( 'Leave a message.', 'kudos-donations' ), 'sanitize_textarea_field' ),
			self::PAYMENT_TITLE              => $this->make_schema_field( FieldType::STRING, __( 'Payment', 'kudos-donations' ), 'sanitize_text_field' ),
			self::PAYMENT_DESCRIPTION        => $this->make_schema_field( FieldType::STRING, __( 'By clicking donate you agree to the following payment:', 'kudos-donations' ), 'sanitize_textarea_field' ),
			self::RETURN_MESSAGE_TITLE       => $this->make_schema_field( FieldType::STRING, __( 'Payment received', 'kudos-donations' ), 'sanitize_text_field' ),
			self::RETURN_MESSAGE_TEXT        => $this->make_schema_field( FieldType::STRING, __( 'Thank you for your donation!', 'kudos-donations' ), 'sanitize_textarea_field' ),
			self::CREATED_AT                 => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::UPDATED_AT                 => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
		];
	}

	/**
	 * Returns linked transactions.
	 *
	 * @param array $campaign The campaign array.
	 * @param array $columns Columns to return.
	 */
	public function get_transactions( array $campaign, array $columns = [ '*' ] ): ?array {
		$campaign_id = $campaign[ self::ID ] ?? null;
		if ( ! $campaign_id ) {
			return null;
		}
		return $this->get_repository( TransactionRepository::class )
			->find_by( [ 'campaign_id' => $campaign_id ], $columns );
	}

	/**
	 * Returns the total donations for supplied campaign.
	 *
	 * @param array $campaign The campaign array.
	 */
	public function get_total( array $campaign ): float {
		$transactions = $this->get_transactions( $campaign, [ 'status', 'value' ] );
		return array_sum(
			array_map(
				function ( $item ) {
					return 'paid' === $item['status'] ? (float) $item['value'] : 0.00;
				},
				$transactions
			)
		);
	}
}
