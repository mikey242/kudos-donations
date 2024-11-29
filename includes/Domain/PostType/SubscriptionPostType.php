<?php
/**
 * Subscription Post Type.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\PostType;

use IseardMedia\Kudos\Domain\HasAdminColumns;
use IseardMedia\Kudos\Domain\HasMetaFieldsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;

class SubscriptionPostType extends AbstractCustomPostType implements HasMetaFieldsInterface, HasAdminColumns {

	/**
	 * Meta field constants.
	 */
	public const META_FIELD_VALUE                  = 'value';
	public const META_FIELD_CURRENCY               = 'currency';
	public const META_FIELD_FREQUENCY              = 'frequency';
	public const META_FIELD_YEARS                  = 'years';
	public const META_FIELD_STATUS                 = 'status';
	public const META_FIELD_CUSTOMER_ID            = 'customer_id';
	public const META_FIELD_TRANSACTION_ID         = 'transaction_id';
	public const META_FIELD_VENDOR_SUBSCRIPTION_ID = 'vendor_subscription_id';

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'kudos_subscription';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_description(): string {
		return 'Kudos Subscription';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_singular_name(): string {
		return _x( 'Subscription', 'Subscription post type singular name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_plural_name(): string {
		return _x( 'Subscriptions', 'Subscription post type plural name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_icon(): string {
		return 'dashicons-update';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_meta_config(): array {
		return [
			self::META_FIELD_VALUE                  => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'absint',
			],
			self::META_FIELD_CURRENCY               => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_FREQUENCY              => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_YEARS                  => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'absint',
			],
			self::META_FIELD_STATUS                 => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_CUSTOMER_ID            => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_TRANSACTION_ID         => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_VENDOR_SUBSCRIPTION_ID => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_columns_config(): array {
		return [
			'donor'                                 => [
				'value_type' => FieldType::EMAIL,
				'label'      => __( 'Donor', 'kudos-donations' ),
				'value'      => function ( $subscription_id ) {
					$transaction_id = get_post_meta( $subscription_id, SubscriptionPostType::META_FIELD_TRANSACTION_ID, true );
					if ( $transaction_id ) {
						$donor_id = get_post_meta( $transaction_id, TransactionPostType::META_FIELD_DONOR_ID, true );
						if ( $donor_id ) {
							return get_post_meta( $donor_id, DonorPostType::META_FIELD_EMAIL, true );
						}
					}
					return null;
				},
			],
			'ID'                                    => [
				'value_type' => FieldType::STRING,
				'value'      => function ( $subscription_id ) {
					return Utils::get_formatted_id( $subscription_id );
				},
			],
			self::META_FIELD_VENDOR_SUBSCRIPTION_ID => [
				'value_type' => FieldType::STRING,
				'label'      => __( 'Vendor ID', 'kudos-donations' ),
			],
			self::META_FIELD_VALUE                  => [
				'value_type' => FieldType::INTEGER,
				'label'      => __( 'Amount', 'kudos-donations' ),
				'value'      => function ( $transaction_id ) {
					$value = get_post_meta( $transaction_id, TransactionPostType::META_FIELD_VALUE, true );
					return Utils::format_value_for_display( $value );
				},
			],
			self::META_FIELD_CURRENCY               => [
				'value_type' => FieldType::STRING,
				'label'      => __( 'Currency', 'kudos-donations' ),
			],
			self::META_FIELD_FREQUENCY              => [
				'value_type' => FieldType::INTEGER,
				'label'      => __( 'Frequency', 'kudos-donations' ),
			],
			self::META_FIELD_YEARS                  => [
				'value_type' => FieldType::INTEGER,
				'label'      => __( 'Length', 'kudos-donations' ),
			],
			'status'                                => [
				'value_type' => FieldType::STRING,
				'label'      => __( 'Status', 'kudos-donations' ),
			],
		];
	}
}
