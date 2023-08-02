<?php
/**
 * Campaign Post Type
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\PostType;

use IseardMedia\Kudos\Domain\HasMetaFieldsInterface;
use IseardMedia\Kudos\Enum\FieldType;

class CampaignPostType extends AbstractCustomPostType implements HasMetaFieldsInterface {

	protected const SHOW_IN_REST = true;
	protected const CAPABILITIES = [ 'create_posts' => true ];

	/**
	 * Meta field constants.
	 */
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
	public const META_FIELD_ADDRESS_ENABLED          = 'address_enabled';
	public const META_FIELD_ADDRESS_REQUIRED         = 'address_required';
	public const META_FIELD_MESSAGE_ENABLED          = 'message_enabled';
	public const META_FIELD_AMOUNT_TYPE              = 'amount_type';
	public const META_FIELD_FIXED_AMOUNTS            = 'fixed_amounts';
	public const META_FIELD_MINIMUM_DONATION         = 'minimum_donation';
	public const META_FIELD_DONATION_TYPE            = 'donation_type';
	public const META_FIELD_THEME_COLOR              = 'theme_color';
	public const META_FIELD_TERMS_LINK               = 'terms_link';
	public const META_FIELD_PRIVACY_LINK             = 'privacy_link';
	public const META_FIELD_SHOW_RETURN_MESSAGE      = 'show_return_message';
	public const META_FIELD_USE_CUSTOM_RETURN_URL    = 'use_custom_return_url';
	public const META_FIELD_CUSTOM_RETURN_URL        = 'custom_return_url';
	public const META_FIELD_RETURN_MESSAGE_TITLE     = 'return_message_title';
	public const META_FIELD_RETURN_MESSAGE_TEXT      = 'return_message_text';

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
	public static function get_meta_config(): array {
		return [
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
			self::META_FIELD_AMOUNT_TYPE              => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'open',
			],
			self::META_FIELD_FIXED_AMOUNTS            => [
				'type'              => FieldType::STRING,
				'single'            => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_MINIMUM_DONATION         => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'sanitize_float',
				'default'           => 1,
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
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			self::META_FIELD_RETURN_MESSAGE_TITLE     => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_RETURN_MESSAGE_TEXT      => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}
}