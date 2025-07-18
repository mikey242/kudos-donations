<?php
/**
 * Campaign Post Type
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\PostType;

use IseardMedia\Kudos\Domain\HasMetaFieldsInterface;
use IseardMedia\Kudos\Domain\HasRestFieldsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Enum\PaymentStatus;

class CampaignPostType extends AbstractCustomPostType implements HasMetaFieldsInterface, HasRestFieldsInterface {

	/**
	 * Meta field constants.
	 */
	public const META_FIELD_CURRENCY                 = 'currency';
	public const META_FIELD_GOAL                     = 'goal';
	public const META_FIELD_SHOW_GOAL                = 'show_goal';
	public const META_FIELD_ADDITIONAL_FUNDS         = 'additional_funds';
	public const META_FIELD_INITIAL_TITLE            = 'initial_title';
	public const META_FIELD_INITIAL_DESCRIPTION      = 'initial_description';
	public const META_FIELD_SUBSCRIPTION_TITLE       = 'subscription_title';
	public const META_FIELD_SUBSCRIPTION_DESCRIPTION = 'subscription_description';
	public const META_FIELD_ADDRESS_TITLE            = 'address_title';
	public const META_FIELD_ADDRESS_DESCRIPTION      = 'address_description';
	public const META_FIELD_MESSAGE_TITLE            = 'message_title';
	public const META_FIELD_MESSAGE_DESCRIPTION      = 'message_description';
	public const META_FIELD_PAYMENT_TITLE            = 'payment_title';
	public const META_FIELD_PAYMENT_DESCRIPTION      = 'payment_description';
	public const META_FIELD_EMAIL_ENABLED            = 'email_enabled';
	public const META_FIELD_EMAIL_REQUIRED           = 'email_required';
	public const META_FIELD_NAME_ENABLED             = 'name_enabled';
	public const META_FIELD_NAME_REQUIRED            = 'name_required';
	public const META_FIELD_ADDRESS_ENABLED          = 'address_enabled';
	public const META_FIELD_ADDRESS_REQUIRED         = 'address_required';
	public const META_FIELD_MESSAGE_ENABLED          = 'message_enabled';
	public const META_FIELD_MESSAGE_REQUIRED         = 'message_required';
	public const META_FIELD_AMOUNT_TYPE              = 'amount_type';
	public const META_FIELD_FIXED_AMOUNTS            = 'fixed_amounts';
	public const META_FIELD_MINIMUM_DONATION         = 'minimum_donation';
	public const META_FIELD_MAXIMUM_DONATION         = 'maximum_donation';
	public const META_FIELD_DONATION_TYPE            = 'donation_type';
	public const META_FREQUENCY_OPTIONS              = 'frequency_options';
	public const META_FIELD_THEME_COLOR              = 'theme_color';
	public const META_FIELD_TERMS_LINK               = 'terms_link';
	public const META_FIELD_PRIVACY_LINK             = 'privacy_link';
	public const META_FIELD_SHOW_RETURN_MESSAGE      = 'show_return_message';
	public const META_FIELD_USE_CUSTOM_RETURN_URL    = 'use_custom_return_url';
	public const META_FIELD_CUSTOM_RETURN_URL        = 'custom_return_url';
	public const META_FIELD_RETURN_MESSAGE_TITLE     = 'return_message_title';
	public const META_FIELD_RETURN_MESSAGE_TEXT      = 'return_message_text';
	public const META_FIELD_CUSTOM_STYLES            = 'custom_styles';
	public const META_PAYMENT_DESCRIPTION_FORMAT     = 'payment_description_format';

	/**
	 * Rest field constants.
	 */
	public const REST_FIELD_TOTAL = 'total';

	/**
	 * {@inheritDoc}
	 */
	protected function get_show_in_rest(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_capabilities(): array {
		return [
			'create_posts' => 'edit_posts',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'kudos_campaign';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_description(): string {
		return 'Kudos Campaign';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_singular_name(): string {
		return _x( 'Campaign', 'Campaign post type singular name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_plural_name(): string {
		return _x( 'Campaigns', 'Campaign post type plural name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_icon(): string {
		return 'dashicons-megaphone';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_meta_config(): array {
		return [
			self::META_FIELD_CURRENCY                 => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'EUR',
			],
			self::META_FIELD_GOAL                     => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_SHOW_GOAL                => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			self::META_FIELD_ADDITIONAL_FUNDS         => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_INITIAL_TITLE            => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => __( 'Support us!', 'kudos-donations' ),
			],
			self::META_FIELD_INITIAL_DESCRIPTION      => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => __( 'Your support is greatly appreciated and will help to keep us going.', 'kudos-donations' ),
			],
			self::META_FIELD_SUBSCRIPTION_TITLE       => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => __( 'Subscription', 'kudos-donations' ),
			],
			self::META_FIELD_SUBSCRIPTION_DESCRIPTION => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => __( 'How often would you like to donate?', 'kudos-donations' ),
			],
			self::META_FIELD_ADDRESS_TITLE            => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => __( 'Address', 'kudos-donations' ),
			],
			self::META_FIELD_ADDRESS_DESCRIPTION      => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => __( 'Please fill in your address', 'kudos-donations' ),
			],
			self::META_FIELD_MESSAGE_TITLE            => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => __( 'Message', 'kudos-donations' ),
			],
			self::META_FIELD_MESSAGE_DESCRIPTION      => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => __( 'Leave a message.', 'kudos-donations' ),
			],
			self::META_FIELD_PAYMENT_TITLE            => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => __( 'Payment', 'kudos-donations' ),
			],
			self::META_FIELD_PAYMENT_DESCRIPTION      => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => __( 'By clicking donate you agree to the following payment:', 'kudos-donations' ),
			],

			self::META_FIELD_EMAIL_ENABLED            => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
			],
			self::META_FIELD_EMAIL_REQUIRED           => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
			],
			self::META_FIELD_NAME_ENABLED             => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
			],
			self::META_FIELD_NAME_REQUIRED            => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
			],
			self::META_FIELD_ADDRESS_ENABLED          => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			self::META_FIELD_ADDRESS_REQUIRED         => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			self::META_FIELD_MESSAGE_ENABLED          => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			self::META_FIELD_MESSAGE_REQUIRED         => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			self::META_FIELD_AMOUNT_TYPE              => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'fixed',
			],
			self::META_FIELD_FIXED_AMOUNTS            => [
				'type'         => FieldType::ARRAY,
				'single'       => true,
				'show_in_rest' => [
					'schema' => [
						'type'  => FieldType::ARRAY,
						'items' => [
							'type' => 'string',
						],
					],
				],
				'default'      => [ '5', '10', '25', '50' ],
			],
			self::META_FIELD_MINIMUM_DONATION         => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'sanitize_float',
				'default'           => 1,
			],
			self::META_FIELD_MAXIMUM_DONATION         => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'sanitize_float',
				'default'           => 5000,
			],
			self::META_FIELD_DONATION_TYPE            => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'oneoff',
			],
			self::META_FIELD_THEME_COLOR              => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '#ff9f1c',
			],
			self::META_FIELD_TERMS_LINK               => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'esc_url_raw',
			],
			self::META_FIELD_PRIVACY_LINK             => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'esc_url_raw',
			],
			self::META_FIELD_SHOW_RETURN_MESSAGE      => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			self::META_FIELD_USE_CUSTOM_RETURN_URL    => [
				'type'              => FieldType::BOOLEAN,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			self::META_FIELD_CUSTOM_RETURN_URL        => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'esc_url_raw',
			],
			self::META_FIELD_RETURN_MESSAGE_TITLE     => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => __( 'Payment received', 'kudos-donations' ),
			],
			self::META_FIELD_RETURN_MESSAGE_TEXT      => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => __( 'Thank you for your donation!', 'kudos-donations' ),
			],
			self::META_FIELD_CUSTOM_STYLES            => [
				'type' => FieldType::STRING,
			],
			self::META_PAYMENT_DESCRIPTION_FORMAT     => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => __( 'Donation ({{campaign_name}}) - {{order_id}}', 'kudos-donations' ),
			],
			self::META_FREQUENCY_OPTIONS              => [
				'type'         => FieldType::OBJECT,
				'single'       => true,
				'show_in_rest' => [
					'schema' => [
						'type'       => FieldType::OBJECT,
						'properties' => [
							'12 months' => [
								'type' => 'string',
							],
							'3 months'  => [
								'type' => 'string',
							],
							'1 month'   => [
								'type' => 'string',
							],
						],
					],
				],
				'default'      =>
					apply_filters(
						'kudos_frequency_options',
						[
							'12 months' => __( 'Yearly', 'kudos-donations' ),
							'3 months'  => __( 'Quarterly', 'kudos-donations' ),
							'1 month'   => __( 'Monthly', 'kudos-donations' ),
						]
					),
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_rest_fields(): array {
		return [
			self::REST_FIELD_TOTAL => [
				'get_callback' => function ( $item ) {

					$campaign_id = $item['id'];
					return $this->get_total( $campaign_id );
				},
			],
		];
	}

	/**
	 * Returns the total donated to the specified campaign.
	 *
	 * @param int $campaign_id The post ID of the campaign.
	 */
	private function get_total( int $campaign_id ): int {
		$transactions = TransactionPostType::get_posts(
			[
				TransactionPostType::META_FIELD_CAMPAIGN_ID => $campaign_id,
				TransactionPostType::META_FIELD_STATUS => PaymentStatus::PAID,
			]
		);

		$values = array_column( $transactions, TransactionPostType::META_FIELD_VALUE );
		$total  = array_sum( $values );

		$additional_funds = get_post_meta( $campaign_id, self::META_FIELD_ADDITIONAL_FUNDS, true );
		return (int) $total + (int) $additional_funds ?? 0;
	}
}
