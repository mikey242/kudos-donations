<?php
/**
 * Donor repository.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Repository;

use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Domain\Table\DonorsTable;

/**
 * @extends BaseRepository<DonorEntity>
 */
class DonorRepository extends BaseRepository {

	/**
	 * {@inheritDoc}
	 */
	public static function get_table_name(): string {
		return DonorsTable::get_name();
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
	 *
	 * @return class-string<DonorEntity>
	 */
	protected function get_entity_class(): string {
		return DonorEntity::class;
	}
}
