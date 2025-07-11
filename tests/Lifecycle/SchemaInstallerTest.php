<?php
/**
 * Tests for SchemaInstaller.
 */

namespace Lifecycle;

use BaseTestCase;
use IseardMedia\Kudos\Domain\Repository\SchemaInstaller;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;

/**
 * @covers \IseardMedia\Kudos\Domain\Repository\SchemaInstaller
 */
class SchemaInstallerTest extends BaseTestCase {

	/**
	 * Ensure all Kudos tables are created.
	 */
	public function test_create_schema_creates_all_tables(): void {
		foreach (SchemaInstaller::get_tables() as $table) {
			$this->assertTrue(
				$this->wpdb->table_exists($table),
				"Table does not exist: " . $this->wpdb->table($table)
			);
		}
	}

	/**
	 * Ensure important columns exist in kudos_campaigns.
	 */
	public function test_campaigns_table_has_expected_columns(): void {
		$this->assertTableHasColumns(
			CampaignRepository::get_table_name(),
			$this->get_repository(CampaignRepository::class)->get_all_fields()
		);
	}

	/**
	 * Ensure important columns exist in kudos_donors.
	 */
	public function test_donors_table_has_expected_columns(): void {
		$this->assertTableHasColumns(
			DonorRepository::get_table_name(),
			$this->get_repository(DonorRepository::class)->get_all_fields()
		);
	}

	/**
	 * Ensure important columns exist in kudos_transactions.
	 */
	public function test_transactions_table_has_expected_columns(): void {
		$this->assertTableHasColumns(
			TransactionRepository::get_table_name(),
			$this->get_repository(TransactionRepository::class)->get_all_fields()
		);
	}

	/**
	 * Ensure important columns exist in kudos_subscriptions.
	 */
	public function test_subscriptions_table_has_expected_columns(): void {
		$this->assertTableHasColumns(
			SubscriptionRepository::get_table_name(),
			$this->get_repository(SubscriptionRepository::class)->get_all_fields()
		);
	}

	/**
	 * Check that specified table contains the specified columns.
	 *
	 * @param string $table The name of the table to check.
	 * @param array $expected_columns Array of columns to look for.
	 */
	protected function assertTableHasColumns( string $table, array $expected_columns ): void {
		$columns = $this->wpdb->get_results("SHOW COLUMNS FROM {$this->wpdb->table($table)}", ARRAY_A);
		$column_names = array_column($columns, 'Field');

		foreach ( $expected_columns as $column ) {
			$this->assertContains($column, $column_names, "Missing expected column '$column' in '$table'");
		}
	}
}
