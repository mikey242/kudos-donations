<?php
/**
 * Transaction repository.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Repository;

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Lifecycle\SchemaInstaller;

class TransactionRepository extends BaseRepository {

	/**
	 * {@inheritDoc}
	 */
	protected function get_table_name(): string {
		return SchemaInstaller::TABLE_TRANSACTIONS;
	}

	public function get_total_by_campaign_id( int $campaign_id ): float {
		$table = $this->wpdb->table( 'kudos_transactions' );

		$sql = $this->wpdb->prepare(
			"SELECT SUM(value) FROM {$table} WHERE campaign_id = %d AND status = %s",
			$campaign_id,
			'paid'
		);

		$total = $this->wpdb->get_var( $sql );

		return (float) $total ?? 0;
	}

	public function get_total_by_donor_id( int $donor_id ): float {
		$table = $this->wpdb->table( 'kudos_transactions' );

		$sql = $this->wpdb->prepare(
			"SELECT SUM(value) FROM {$table} WHERE donor_id = %d AND status = %s",
			$donor_id,
			'paid'
		);

		$total = $this->wpdb->get_var( $sql );

		return (float) ( $total ?? 0 );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_column_schema(): array {
		return [
			'status' => FieldType::STRING,
		];
	}
}
