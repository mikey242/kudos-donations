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

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Infrastructure\Domain\AbstractCustomPostType;
use IseardMedia\Kudos\Infrastructure\Domain\HasAdminColumns;
use IseardMedia\Kudos\Infrastructure\Domain\HasMetaFieldsInterface;

class TransactionPostType extends AbstractCustomPostType implements HasMetaFieldsInterface, HasAdminColumns {

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
			'value'             => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'absint',
			],
			'currency'          => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'status'            => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'method'            => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'mode'              => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'sequence_type'     => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'donor_id'          => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'absint',
			],
			'vendor_payment_id' => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'order_id'          => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'campaign_id'       => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'refunds'           => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'message'           => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'vendor'            => [
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
			'order_id'          => [
				'value_type' => FieldType::STRING,
				'label'      => __( 'Order ID', 'kudos-donations' ),
			],
			'vendor_payment_id' => [
				'value_type' => FieldType::STRING,
				'label'      => __( 'Vendor ID', 'kudos-donations' ),
			],
			'value'             => [
				'value_type' => FieldType::INTEGER,
				'label'      => __( 'Value', 'kudos-donations' ),
			],
			'campaign'          => [
				'value_type'     => FieldType::STRING,
				'label'          => __( 'Campaign', 'kudos-donations' ),
				'value_callback' => function( $transaction_id ): ?string {
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
			'currency'          => [
				'value_type' => FieldType::STRING,
				'label'      => __( 'Currency', 'kudos-donations' ),
			],
			'status'            => [
				'value_type'     => FieldType::STRING,
				'label'          => __( 'Status', 'kudos-donations' ),
				'value_callback' => function( $transaction_id ) {
					$status = get_post_meta( $transaction_id, 'status', true );
					return match ($status) {
						PaymentStatus::PAID => __( 'Paid', 'kudos-donations' ) . '<span class="dashicons dashicons-yes"></span>',
						PaymentStatus::OPEN => __( 'Open', 'kudos-donations' ),
						PaymentStatus::CANCELED => __( 'Canceled', 'kudos-donations' ) . '<span class="dashicons dashicons-no"></span>',
						default => $status
					};
				},
			],
			'message'           => [
				'value_type' => FieldType::STRING,
				'label'      => __( 'Message', 'kudos-donations' ),
			],
		];
	}
}
