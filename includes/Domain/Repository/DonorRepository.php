<?php
/**
 * Donor repository.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Repository;

use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Enum\FieldType;

class DonorRepository extends BaseRepository {

	/**
	 * {@inheritDoc}
	 */
	public static function get_table_name(): string {
		return 'kudos_donors';
	}

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
			'email'              => $this->make_schema_field( FieldType::STRING, 'sanitize_email' ),
			'mode'               => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'name'               => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'business_name'      => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'street'             => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'postcode'           => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'city'               => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'country'            => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'locale'             => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
			'vendor_customer_id' => $this->make_schema_field( FieldType::STRING, 'sanitize_text_field' ),
		];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return class-string<DonorEntity>
	 */
	protected function get_entity_class(): string {
		return DonorEntity::class;
	}
}
