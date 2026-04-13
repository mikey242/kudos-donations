<?php
/**
 * MigrationHandler tests.
 */

namespace IseardMedia\Kudos\Tests\Migrations;

use IseardMedia\Kudos\Container\Handler\MigrationHandler;
use IseardMedia\Kudos\Helper\Localization;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Container\Handler\MigrationHandler
 */
class MigrationHandlerTest extends BaseTestCase {

	protected function tearDown(): void {
		parent::tearDown();
		Localization::reset();
	}

	/**
	 * Create a mock migration with a given version.
	 */
	private function create_mock_migration( string $version ): MigrationInterface {
		$mock = $this->createMock( MigrationInterface::class );
		$mock->method( 'get_version' )->willReturn( $version );
		return $mock;
	}

	/**
	 * Test that migrations are sorted by version.
	 */
	public function test_migrations_are_sorted_by_version(): void {
		$handler = new MigrationHandler(
			[
				$this->create_mock_migration( '4.2.0' ),
				$this->create_mock_migration( '4.0.0' ),
				$this->create_mock_migration( '4.1.3' ),
			]
		);

		$migrations = $handler->get_migrations();

		$this->assertSame( '4.0.0', $migrations[0]->get_version() );
		$this->assertSame( '4.1.3', $migrations[1]->get_version() );
		$this->assertSame( '4.2.0', $migrations[2]->get_version() );
	}

	/**
	 * Test that an empty iterable results in no migrations.
	 */
	public function test_empty_migrations(): void {
		$handler = new MigrationHandler( [] );

		$this->assertEmpty( $handler->get_migrations() );
	}

	/**
	 * Test that add() appends a migration.
	 */
	public function test_add_appends_migration(): void {
		$handler = new MigrationHandler( [] );
		$handler->add( $this->create_mock_migration( '5.0.0' ) );

		$migrations = $handler->get_migrations();

		$this->assertCount( 1, $migrations );
		$this->assertSame( '5.0.0', $migrations[0]->get_version() );
	}

	/**
	 * Test should_upgrade returns false when db version equals target.
	 */
	public function test_should_upgrade_returns_false_when_current(): void {
		update_option( MigrationHandler::SETTING_DB_VERSION, KUDOS_DB_VERSION );

		$handler = new MigrationHandler( [] );

		$this->assertFalse( $handler->should_upgrade() );
	}

	/**
	 * Test should_upgrade returns true when db version is older.
	 */
	public function test_should_upgrade_returns_true_when_outdated(): void {
		update_option( MigrationHandler::SETTING_DB_VERSION, '1.0.0' );

		$handler = new MigrationHandler( [] );

		$this->assertTrue( $handler->should_upgrade() );
	}

	/**
	 * Test should_upgrade returns false when db version is empty.
	 */
	public function test_should_upgrade_returns_false_when_empty(): void {
		delete_option( MigrationHandler::SETTING_DB_VERSION );

		$handler = new MigrationHandler( [] );

		$this->assertFalse( $handler->should_upgrade() );
	}

	/**
	 * Test that a migration not in history is returned as pending.
	 */
	public function test_get_pending_migrations_returns_unrun_migrations(): void {
		update_option( MigrationHandler::SETTING_MIGRATION_HISTORY, [] );

		$handler = new MigrationHandler(
			[ $this->create_mock_migration( '4.2.0' ) ]
		);

		$this->assertCount( 1, $handler->get_pending_migrations() );
	}

	/**
	 * Test that a migration already in history is excluded from pending.
	 */
	public function test_get_pending_migrations_excludes_completed_migrations(): void {
		update_option( MigrationHandler::SETTING_MIGRATION_HISTORY, [ '4.2.0' ] );

		$handler = new MigrationHandler(
			[ $this->create_mock_migration( '4.2.0' ) ]
		);

		$this->assertEmpty( $handler->get_pending_migrations() );
	}

	/**
	 * Test that only the unrun migration is returned when history is partial.
	 */
	public function test_get_pending_migrations_returns_only_unrun(): void {
		update_option( MigrationHandler::SETTING_MIGRATION_HISTORY, [ '4.0.0', '4.1.3' ] );

		$handler = new MigrationHandler(
			[
				$this->create_mock_migration( '4.0.0' ),
				$this->create_mock_migration( '4.1.3' ),
				$this->create_mock_migration( '4.2.0' ),
			]
		);

		$pending = array_values( $handler->get_pending_migrations() );

		$this->assertCount( 1, $pending );
		$this->assertSame( '4.2.0', $pending[0]->get_version() );
	}

	/**
	 * Returns the total number of admin_notices callbacks across all priorities.
	 */
	private function count_admin_notices_callbacks(): int {
		global $wp_filter;
		if ( ! isset( $wp_filter['admin_notices'] ) ) {
			return 0;
		}
		return array_sum( array_map( 'count', $wp_filter['admin_notices']->callbacks ) );
	}

	/**
	 * Test that register() adds an admin_notices hook when an upgrade is needed
	 * and the current page is not a Kudos admin page.
	 */
	public function test_register_adds_notice_when_upgrade_needed_outside_kudos(): void {
		update_option( MigrationHandler::SETTING_DB_VERSION, '4.1.3' );
		unset( $_GET['page'] ); // Simulate being outside a Kudos admin page.

		$handler = new MigrationHandler( [] );
		$before  = $this->count_admin_notices_callbacks();

		$handler->register();

		$this->assertGreaterThan( $before, $this->count_admin_notices_callbacks(), 'An admin_notices hook should have been added.' );
	}

	/**
	 * Test that register() does NOT add a notice when on a Kudos admin page
	 * (the React modal handles the prompt instead).
	 */
	public function test_register_suppresses_notice_on_kudos_admin_page(): void {
		update_option( MigrationHandler::SETTING_DB_VERSION, '4.1.3' );
		$_GET['page'] = 'kudos-campaigns'; // Simulate being on a Kudos admin page.

		$handler = new MigrationHandler( [] );
		$before  = $this->count_admin_notices_callbacks();

		$handler->register();

		unset( $_GET['page'] );

		$this->assertSame( $before, $this->count_admin_notices_callbacks(), 'No admin_notices hook should be added on a Kudos admin page.' );
	}

	/**
	 * Test that register() adds needsUpgrade=true to the localization filter
	 * when an upgrade is pending.
	 */
	public function test_register_sets_needs_upgrade_in_localization_when_pending(): void {
		update_option( MigrationHandler::SETTING_DB_VERSION, '4.1.3' );

		$handler = new MigrationHandler( [] );
		$handler->register();

		$data = Localization::get_admin();

		$this->assertTrue( $data['needsUpgrade'] );
	}

	/**
	 * Test that register() does not add needsUpgrade to the localization filter
	 * when no upgrade is pending (the false default is provided by AbstractReactSubPage).
	 */
	public function test_register_sets_needs_upgrade_false_when_current(): void {
		update_option( MigrationHandler::SETTING_DB_VERSION, KUDOS_DB_VERSION );

		$handler = new MigrationHandler( [] );
		$handler->register();

		$data = Localization::get_admin();

		$this->assertArrayNotHasKey( 'needsUpgrade', $data );
	}
}
