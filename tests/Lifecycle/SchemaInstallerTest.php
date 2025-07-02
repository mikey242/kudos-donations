<?php
/**
 * Tests for SchemaInstaller.
 */

namespace Lifecycle;

use BaseTestCase;
use IseardMedia\Kudos\Lifecycle\SchemaInstaller;
use IseardMedia\Kudos\Repository\CampaignRepository;
use IseardMedia\Kudos\Repository\DonorRepository;
use IseardMedia\Kudos\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Repository\TransactionRepository;

/**
 * @covers \IseardMedia\Kudos\Lifecycle\SchemaInstaller
 */
class SchemaInstallerTest extends BaseTestCase {
	private SchemaInstaller $schema;

	public function set_up(): void {
		parent::set_up();

		$this->schema = new SchemaInstaller($this->wpdb);

		// Drop all tables to test creation from scratch
		foreach ($this->get_kudos_table_names() as $table) {
			$this->wpdb->query("DROP TABLE IF EXISTS {$this->wpdb->table($table)}");
		}
	}

	private function get_kudos_table_names(): array {
			return SchemaInstaller::TABLE_NAMES;
	}

	/**
	 * Ensure all Kudos tables are created.
	 */
	public function test_create_schema_creates_all_tables(): void {
		$this->schema->create_schema();

		foreach (SchemaInstaller::TABLE_NAMES as $table) {
			$this->assertTrue(
				$this->wpdb->table_exists($table),
				"Failed asserting table '$table' exists"
			);
		}
	}

	/**
	 * Ensure important columns exist in kudos_campaigns.
	 */
	public function test_campaigns_table_has_expected_columns(): void {
		$this->schema->create_campaigns_table();

		$this->assertTableHasColumns(CampaignRepository::TABLE_NAME, [
			'id',
			'title',
			'currency',
			'goal',
		]);
	}

	/**
	 * Ensure important columns exist in kudos_donors.
	 */
	public function test_donors_table_has_expected_columns(): void {
		$this->schema->create_donors_table();

		$this->assertTableHasColumns(DonorRepository::TABLE_NAME, [
			'id',
			'email',
			'country',
		]);
	}

	/**
	 * Ensure important columns exist in kudos_transactions.
	 */
	public function test_transactions_table_has_expected_columns(): void {
		$this->schema->create_transactions_table();

		$this->assertTableHasColumns(TransactionRepository::TABLE_NAME, [
			'id',
			'value',
			'status',
		]);
	}

	/**
	 * Ensure important columns exist in kudos_subscriptions.
	 */
	public function test_subscriptions_table_has_expected_columns(): void {
		$this->schema->create_subscriptions_table();

		$this->assertTableHasColumns(SubscriptionRepository::TABLE_NAME, [
			'id',
			'status',
			'donor_id',
			'frequency',
		]);
	}
}
