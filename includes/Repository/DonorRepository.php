<?php
/**
 * Donor repository.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Repository;

use IseardMedia\Kudos\Enum\FieldType;

class DonorRepository extends BaseRepository {

	/**
	 * Field constants.
	 */
	public const TABLE_NAME = 'kudos_donors';

	/**
	 * Field constants.
	 */
	public const EMAIL              = 'email';
	public const MODE               = 'mode';
	public const NAME               = 'name';
	public const BUSINESS_NAME      = 'business_name';
	public const STREET             = 'street';
	public const POSTCODE           = 'postcode';
	public const CITY               = 'city';
	public const COUNTRY            = 'country';
	public const LOCALE             = 'locale';
	public const VENDOR_CUSTOMER_ID = 'vendor_customer_id';

	/**
	 * {@inheritDoc}
	 */
	public static function get_singular_name(): string {
		return _x( 'Donor', 'Donor post type singular name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_plural_name(): string {
		return _x( 'Donors', 'Donor post type plural name', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_additional_column_schema(): array {
		return [
			self::EMAIL              => $this->make_schema_field( FieldType::STRING, null, 'sanitize_email' ),
			self::MODE               => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::NAME               => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::BUSINESS_NAME      => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::STREET             => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::POSTCODE           => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::CITY               => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::COUNTRY            => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::LOCALE             => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
			self::VENDOR_CUSTOMER_ID => $this->make_schema_field( FieldType::STRING, null, 'sanitize_text_field' ),
		];
	}
}
