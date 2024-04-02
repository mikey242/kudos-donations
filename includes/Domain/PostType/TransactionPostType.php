<?php
/**
 * Transaction Post Type.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\PostType;

use IseardMedia\Kudos\Domain\HasAdminColumns;
use IseardMedia\Kudos\Domain\HasMetaFieldsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Enum\PaymentStatus;

class TransactionPostType extends AbstractCustomPostType implements HasMetaFieldsInterface, HasAdminColumns {

	/**
	 * Meta field constants.
	 */
	public const META_FIELD_VALUE              = 'value';
	public const META_FIELD_CURRENCY           = 'currency';
	public const META_FIELD_STATUS             = 'status';
	public const META_FIELD_METHOD             = 'method';
	public const META_FIELD_MODE               = 'mode';
	public const META_FIELD_SEQUENCE_TYPE      = 'sequence_type';
	public const META_FIELD_DONOR_ID           = 'donor_id';
	public const META_FIELD_VENDOR_PAYMENT_ID  = 'vendor_payment_id';
	public const META_FIELD_CAMPAIGN_ID        = 'campaign_id';
	public const META_FIELD_REFUNDS            = 'refunds';
	public const META_FIELD_MESSAGE            = 'message';
	public const META_FIELD_VENDOR             = 'vendor';
	public const META_FIELD_VENDOR_CUSTOMER_ID = 'vendor_customer_id';
	public const META_FIELD_SUBSCRIPTION_ID    = 'subscription_id';

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'kudos_transaction';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_description(): string {
		return 'Kudos Transaction';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_singular_name(): string {
		return _x( 'Transaction', 'Transaction post type singular name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_plural_name(): string {
		return _x( 'Transactions', 'Transaction post type plural name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_meta_config(): array {
		return [
			self::META_FIELD_VALUE              => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'absint',
			],
			self::META_FIELD_CURRENCY           => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_STATUS             => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_METHOD             => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_MODE               => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_SEQUENCE_TYPE      => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_DONOR_ID           => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'absint',
			],
			self::META_FIELD_VENDOR_PAYMENT_ID  => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_CAMPAIGN_ID        => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_REFUNDS            => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_MESSAGE            => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_VENDOR             => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_VENDOR_CUSTOMER_ID => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_SUBSCRIPTION_ID    => [
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
			'donor'                            => [
				'value_type' => FieldType::EMAIL,
				'label'      => __( 'Donor', 'kudos-donations' ),
				'value'      => function ( $transaction_id ) {
					$donor_id = get_post_meta( $transaction_id, TransactionPostType::META_FIELD_DONOR_ID, true );
					if ( $donor_id ) {
						return get_post_meta( $donor_id, 'email', true );
					}
					return null;
				},
			],
			'ID'                               => [
				'value_type' => FieldType::STRING,
				'value'      => function ( $transaction_id ) {
					return static::get_formatted_id( $transaction_id );
				},
			],
			self::META_FIELD_VENDOR_PAYMENT_ID => [
				'value_type' => FieldType::STRING,
				'label'      => __( 'Vendor ID', 'kudos-donations' ),
			],
			self::META_FIELD_VALUE             => [
				'value_type' => FieldType::INTEGER,
				'label'      => __( 'Amount', 'kudos-donations' ),
			],
			self::META_FIELD_CURRENCY          => [
				'value_type' => FieldType::STRING,
				'label'      => __( 'Currency', 'kudos-donations' ),
			],
			self::META_FIELD_CAMPAIGN_ID       => [
				'value_type' => FieldType::STRING,
				'label'      => __( 'Campaign', 'kudos-donations' ),
				'value'      => function ( $transaction_id ): ?string {
					$campaign_id = get_post_meta( $transaction_id, 'campaign_id', true );
					if ( $campaign_id ) {
						$campaign = get_post( $campaign_id );
						if ( $campaign ) {
							return $campaign->post_title;
						}
					}
					return null;
				},
			],
			self::META_FIELD_STATUS            => [
				'value_type' => FieldType::STRING,
				'label'      => __( 'Status', 'kudos-donations' ),
				'value'      => function ( $transaction_id ) {
					$status = get_post_meta( $transaction_id, 'status', true );

					switch ( $status ) {
						case PaymentStatus::PAID:
							$url         = rest_url( '/kudos/v1/invoice/transaction/' . $transaction_id );
							$status_text = '<a href="' . $url . '">' . __( 'Paid', 'kudos-donations' ) . '</a><span class="dashicons dashicons-yes"></span>';
							break;
						case PaymentStatus::OPEN:
							$status_text = __( 'Open', 'kudos-donations' );
							break;
						case PaymentStatus::CANCELED:
							$status_text = __( 'Canceled', 'kudos-donations' ) . '<span class="dashicons dashicons-no"></span>';
							break;
						default:
							$status_text = $status;
					}

					return $status_text;
				},
			],
			self::META_FIELD_MESSAGE           => [
				'value_type' => FieldType::STRING,
				'label'      => __( 'Message', 'kudos-donations' ),
			],
		];
	}
}
