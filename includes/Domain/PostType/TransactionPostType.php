<?php
/**
 * Transaction Post Type.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\PostType;

use IseardMedia\Kudos\Domain\HasMetaFieldsInterface;
use IseardMedia\Kudos\Domain\HasRestFieldsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;

class TransactionPostType extends AbstractCustomPostType implements HasMetaFieldsInterface, HasRestFieldsInterface {

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
	 * Rest field constants.
	 */
	public const REST_FIELD_DONOR       = 'donor';
	public const REST_FIELD_CAMPAIGN    = 'campaign';
	public const REST_FIELD_INVOICE_URL = 'invoice_url';

	/**
	 * {@inheritDoc}
	 */
	protected function get_show_in_rest(): bool {
		return current_user_can( 'edit_posts' );
	}

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
				'type'              => FieldType::INTEGER,
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
	public function get_rest_fields(): array {
		return [
			self::REST_FIELD_DONOR       => [
				'get_callback' => function ( $transaction ) {
					$donor_id = $transaction['meta'][ self::META_FIELD_DONOR_ID ] ?? null;
					return DonorPostType::get_post_using_rest( $donor_id );
				},
			],
			self::REST_FIELD_CAMPAIGN    => [
				'get_callback' => function ( $transaction ) {
					$campaign_id = $transaction['meta'][ self::META_FIELD_CAMPAIGN_ID ] ?? null;
					return CampaignPostType::get_post_using_rest( $campaign_id );
				},
			],
			self::REST_FIELD_INVOICE_URL => [
				'get_callback' => function ( $transaction ) {
					$transaction_id = $transaction['id'];

					return add_query_arg(
						[
							'post_type'    => self::get_slug(),
							'kudos_action' => 'view_invoice',
							'_wpnonce'     => wp_create_nonce( "view_invoice_$transaction_id" ),
							'id'           => $transaction_id,
						],
						admin_url( 'edit.php' )
					);
				},
			],
		];
	}
}
