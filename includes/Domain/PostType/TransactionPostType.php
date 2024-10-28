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
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Vendor\MollieVendor;

class TransactionPostType extends AbstractCustomPostType implements HasMetaFieldsInterface, HasAdminColumns {

	/**
	 * Meta field constants.
	 */
	public const META_FIELD_VALUE                  = 'value';
	public const META_FIELD_CURRENCY               = 'currency';
	public const META_FIELD_STATUS                 = 'status';
	public const META_FIELD_METHOD                 = 'method';
	public const META_FIELD_MODE                   = 'mode';
	public const META_FIELD_SEQUENCE_TYPE          = 'sequence_type';
	public const META_FIELD_DONOR_ID               = 'donor_id';
	public const META_FIELD_VENDOR_PAYMENT_ID      = 'vendor_payment_id';
	public const META_FIELD_CAMPAIGN_ID            = 'campaign_id';
	public const META_FIELD_REFUNDS                = 'refunds';
	public const META_FIELD_MESSAGE                = 'message';
	public const META_FIELD_VENDOR                 = 'vendor';
	public const META_FIELD_VENDOR_CUSTOMER_ID     = 'vendor_customer_id';
	public const META_FIELD_VENDOR_SUBSCRIPTION_ID = 'vendor_subscription_id';
	public const META_FIELD_INVOICE_NUMBER         = 'invoice_number';
	public const META_FIELD_CHECKOUT_URL           = 'checkout_url';

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
	protected function get_icon(): string {
		return 'dashicons-money-alt';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_meta_config(): array {
		return [
			self::META_FIELD_VALUE                  => [
				'type'              => FieldType::NUMBER,
				'sanitize_callback' => [ Utils::class, 'sanitize_float' ],
			],
			self::META_FIELD_CURRENCY               => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_STATUS                 => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_METHOD                 => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_MODE                   => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_SEQUENCE_TYPE          => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_DONOR_ID               => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'absint',
			],
			self::META_FIELD_VENDOR_PAYMENT_ID      => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_CAMPAIGN_ID            => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_REFUNDS                => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_MESSAGE                => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_VENDOR                 => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_VENDOR_CUSTOMER_ID     => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_VENDOR_SUBSCRIPTION_ID => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_INVOICE_NUMBER         => [
				'type'              => FieldType::INTEGER,
				'sanitize_callback' => 'absint',
			],
			self::META_FIELD_CHECKOUT_URL           => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_url',
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_columns_config(): array {
		return [
			'ID'                         => [
				'value_type' => FieldType::STRING,
				'value'      => function ( $transaction_id ) {
					$post     = get_post( $transaction_id );
					$sequence = $post->{TransactionPostType::META_FIELD_SEQUENCE_TYPE};
					switch ( $sequence ) {
						case 'oneoff':
							$icon = 'money-alt';
							break;
						case 'first':
							$icon = 'calendar';
							break;
						case 'recurring':
							$icon = 'update';
							break;
						default:
							$icon = '';

					}
					return "<span class='dashicons dashicons-$icon'></span> " . Utils::get_formatted_id( $transaction_id );
				},
			],
			'donor'                      => [
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
			self::META_FIELD_VALUE       => [
				'value_type' => FieldType::NUMBER,
				'label'      => __( 'Amount', 'kudos-donations' ),
				'value'      => function ( $transaction_id ) {
					$post     = get_post( $transaction_id );
					$value    = $post->{TransactionPostType::META_FIELD_VALUE};
					$currency = $post->{TransactionPostType::META_FIELD_CURRENCY};
					return Utils::get_currencies()[ $currency ] . Utils::format_value_for_display( $value );
				},
			],
			self::META_FIELD_CAMPAIGN_ID => [
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
			self::META_FIELD_STATUS      => [
				'value_type' => FieldType::STRING,
				'label'      => __( 'Status', 'kudos-donations' ),
				'value'      => function ( $transaction_id ) {
					$status = get_post_meta( $transaction_id, 'status', true );

					switch ( $status ) {
						case PaymentStatus::PAID:
							$url         = add_query_arg(
								[
									'post_type'    => self::get_slug(),
									'kudos_action' => 'view_invoice',
									'_wpnonce'     => wp_create_nonce( "view_invoice_$transaction_id" ),
									'id'           => $transaction_id,
								],
								admin_url( 'edit.php' )
							);
							$status_text = '<a class="button button-secondary kudos-transaction-pdf success" href="' . $url . '"><span style="margin-right: 4px; vertical-align: text-top;" class="dashicons dashicons-media-document"></span><span>' . __( 'Paid', 'kudos-donations' ) . '</span></a>';
							break;
						case PaymentStatus::OPEN:
							$status_text = __( 'Open', 'kudos-donations' );
							break;
						case PaymentStatus::CANCELED:
							$status_text = __( 'Canceled', 'kudos-donations' ) . '<span class="dashicons dashicons-no"></span>';
							break;
						case PaymentStatus::FAILED:
							$status_text = __( 'Failed', 'kudos-donations' ) . '<span class="dashicons dashicons-no"></span>';
							break;
						default:
							$status_text = $status;
					}

					return $status_text;
				},
			],
			self::META_FIELD_METHOD      => [
				'label' => __( 'Method', 'kudos-donations' ),
				'value' => function ( $transaction_id ) {
					$method  = get_post_meta( $transaction_id, TransactionPostType::META_FIELD_METHOD, true );
					$methods = get_option( MollieVendor::SETTING_PAYMENT_METHODS );
					$methods = array_column( $methods, null, 'id' );
					$url     = $methods[ $method ]['image'] ?? null;
					$title   = $methods[ $method ]['description'] ?? null;
					if ( $url ) {
						return '<img title=' . $title . ' src=' . $url . '  alt=' . __( 'Payment method icon', 'kudos-donations' ) . '/>';
					}
					return '';
				},
			],
			self::META_FIELD_MESSAGE     => [
				'value_type' => FieldType::STRING,
				'label'      => __( 'Message', 'kudos-donations' ),
			],
		];
	}
}
