<?php
/**
 * Donor Post Type.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\PostType;

use IseardMedia\Kudos\Domain\HasAdminColumns;
use IseardMedia\Kudos\Domain\HasMetaFieldsInterface;
use IseardMedia\Kudos\Domain\HasRestFieldsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Enum\PaymentStatus;

class DonorPostType extends AbstractCustomPostType implements HasMetaFieldsInterface, HasRestFieldsInterface, HasAdminColumns {

	/**
	 * Meta field constants.
	 */
	public const META_FIELD_EMAIL              = 'email';
	public const META_FIELD_MODE               = 'mode';
	public const META_FIELD_NAME               = 'name';
	public const META_FIELD_BUSINESS_NAME      = 'business_name';
	public const META_FIELD_STREET             = 'street';
	public const META_FIELD_POSTCODE           = 'postcode';
	public const META_FIELD_CITY               = 'city';
	public const META_FIELD_COUNTRY            = 'country';
	public const META_FIELD_VENDOR_CUSTOMER_ID = 'vendor_customer_id';

	/**
	 * Rest field constants.
	 */
	public const REST_FIELD_TOTAL = 'total';

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'kudos_donor';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_description(): string {
		return 'Kudos Donor';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_singular_name(): string {
		return _x( 'Donor', 'Donor post type singular name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_plural_name(): string {
		return _x( 'Donors', 'Donor post type plural name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_meta_config(): array {
		return [
			self::META_FIELD_EMAIL              => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_email',
			],
			self::META_FIELD_MODE               => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_NAME               => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_BUSINESS_NAME      => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_STREET             => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_POSTCODE           => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_CITY               => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_COUNTRY            => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
			],
			self::META_FIELD_VENDOR_CUSTOMER_ID => [
				'type'              => FieldType::STRING,
				'sanitize_callback' => 'sanitize_text_field',
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
					$donor_id = $item['id'];
					return $this->get_total( $donor_id );
				},
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_columns_config(): array {
		return [
			self::META_FIELD_NAME               => [
				'label'      => __( 'Name', 'kudos-donations' ),
				'value_type' => FieldType::STRING,
			],
			self::META_FIELD_EMAIL              => [
				'label'      => __( 'Email', 'kudos-donations' ),
				'value_type' => FieldType::EMAIL,
			],
			self::META_FIELD_VENDOR_CUSTOMER_ID => [
				'label'      => __( 'Vendor ID', 'kudos-donations' ),
				'value_type' => FieldType::STRING,
			],
			'total_donations'                   => [
				'label'      => __( 'Total donated', 'kudos-donations' ),
				'value_type' => FieldType::INTEGER,
				'value'      => function ( $donor_id ) {
					return $this->get_total( $donor_id );
				},
			],
		];
	}

	/**
	 * Returns the total donated by the specified donor.
	 *
	 * @param int $donor_id The post ID of the donor.
	 */
	private function get_total( int $donor_id ): int {
		$transactions = TransactionPostType::get_posts(
			[
				TransactionPostType::META_FIELD_DONOR_ID => $donor_id,
				TransactionPostType::META_FIELD_STATUS   => PaymentStatus::PAID,
			]
		);

		$values = array_column( $transactions, TransactionPostType::META_FIELD_VALUE );
		return array_sum( $values );
	}
}
