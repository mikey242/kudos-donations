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

use IseardMedia\Kudos\Lifecycle\SchemaInstaller;

class DonorRepository extends BaseRepository {

	/**
	 * {@inheritDoc}
	 */
	protected function get_table_name(): string {
		return SchemaInstaller::TABLE_DONORS;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_column_schema(): array {
		return [];
	}
}
