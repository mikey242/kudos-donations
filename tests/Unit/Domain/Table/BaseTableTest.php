<?php
/**
 * BaseTable tests.
 */

namespace IseardMedia\Kudos\Tests\Domain\Table;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Table\CampaignsTable;
use IseardMedia\Kudos\Helper\WpDb;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Domain\Table\BaseTable
 */
class BaseTableTest extends BaseTestCase {

	private CampaignsTable $table;
	private WpDb $wpdb;

	public function set_up(): void {
		parent::set_up();
		$this->table = $this->get_from_container( CampaignsTable::class );
		$this->wpdb  = $this->get_from_container( WpDb::class );

		// Remove WP_UnitTestCase's temporary table filters so real DDL executes.
		remove_filter( 'query', [ $this, '_create_temporary_tables' ] );
		remove_filter( 'query', [ $this, '_drop_temporary_tables' ] );
	}

	public function tear_down(): void {
		// Ensure the real table exists for other tests.
		$this->table->create_table();

		// Restore temporary table filters for WP_UnitTestCase cleanup.
		add_filter( 'query', [ $this, '_create_temporary_tables' ] );
		add_filter( 'query', [ $this, '_drop_temporary_tables' ] );

		parent::tear_down();
	}

	public function test_create_table_creates_table(): void {
		$this->table->drop_table();
		$this->table->create_table();
		$this->assertTrue( $this->wpdb->table_exists( CampaignsTable::get_name() ) );
	}

	public function test_create_table_is_idempotent(): void {
		$campaign_repository = $this->get_from_container( CampaignRepository::class );

		$this->table->create_table();
		$id = $campaign_repository->insert( new CampaignEntity( [ 'title' => 'Survivor' ] ) );

		$this->table->create_table();

		$campaign = $campaign_repository->get( $id );
		$this->assertNotNull( $campaign );
		$this->assertSame( 'Survivor', $campaign->title );
	}

	public function test_on_plugin_activation_creates_table(): void {
		$this->table->drop_table();
		$this->table->on_plugin_activation();
		$this->assertTrue( $this->wpdb->table_exists( CampaignsTable::get_name() ) );
	}

	public function test_has_column_returns_true_for_existing_column(): void {
		$this->assertTrue( $this->table->has_column( 'id' ) );
	}

	public function test_has_column_returns_false_for_nonexistent_column(): void {
		$this->assertFalse( $this->table->has_column( 'nonexistent_column' ) );
	}

	public function test_drop_table_removes_table(): void {
		$this->assertTrue( $this->wpdb->table_exists( CampaignsTable::get_name() ) );

		$this->table->drop_table();
		$this->assertFalse( $this->wpdb->table_exists( CampaignsTable::get_name() ) );
	}
}
