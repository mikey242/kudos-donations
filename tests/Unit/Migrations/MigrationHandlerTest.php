<?php
/**
 * MigrationHandler tests.
 */

namespace IseardMedia\Kudos\Tests\Migrations;

use IseardMedia\Kudos\Container\Handler\MigrationHandler;
use IseardMedia\Kudos\Helper\Localization;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use IseardMedia\Kudos\Notice\NoticeManager;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Container\Handler\MigrationHandler
 */
class MigrationHandlerTest extends BaseTestCase {

	protected function tearDown(): void {
		parent::tearDown();
		Localization::reset();
		NoticeManager::reset();
	}

	/**
	 * Create a mock migration with a given version.
	 */
	private function create_mock_migration( string $version, bool $auto = false ): MigrationInterface {
		$mock = $this->createMock( MigrationInterface::class );
		$mock->method( 'get_version' )->willReturn( $version );
		$mock->method( 'is_auto' )->willReturn( $auto );
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
	 * Test should_upgrade returns false when there are no pending migrations.
	 */
	public function test_should_upgrade_returns_false_when_no_pending(): void {
		$handler = new MigrationHandler( [] );

		$this->assertFalse( $handler->should_upgrade() );
	}

	/**
	 * Test should_upgrade returns false when all pending migrations are auto.
	 */
	public function test_should_upgrade_returns_false_when_only_auto_pending(): void {
		update_option( MigrationHandler::SETTING_MIGRATION_HISTORY, [] );

		$handler = new MigrationHandler(
			[ $this->create_mock_migration( '4.2.3', true ) ]
		);

		$this->assertFalse( $handler->should_upgrade() );
	}

	/**
	 * Test should_upgrade returns true when there is a pending non-auto migration.
	 */
	public function test_should_upgrade_returns_true_when_manual_pending(): void {
		update_option( MigrationHandler::SETTING_MIGRATION_HISTORY, [] );

		$handler = new MigrationHandler(
			[ $this->create_mock_migration( '4.2.0' ) ]
		);

		$this->assertTrue( $handler->should_upgrade() );
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
	 * Test that register() queues a notice when a non-auto migration is
	 * pending and the current page is not a Kudos admin page.
	 */
	public function test_register_adds_notice_when_upgrade_needed_outside_kudos(): void {
		update_option( MigrationHandler::SETTING_MIGRATION_HISTORY, [] );
		unset( $_GET['page'] ); // Simulate being outside a Kudos admin page.

		$handler = new MigrationHandler( [ $this->create_mock_migration( '4.2.0' ) ] );
		$handler->register();

		$this->assertNotEmpty( NoticeManager::get_formatted_notices(), 'A notice should have been queued.' );
	}

	/**
	 * Test that register() does NOT queue a notice when on a Kudos admin page
	 * (the React modal handles the prompt instead).
	 */
	public function test_register_suppresses_notice_on_kudos_admin_page(): void {
		update_option( MigrationHandler::SETTING_MIGRATION_HISTORY, [] );
		$_GET['page'] = 'kudos-campaigns'; // Simulate being on a Kudos admin page.

		$handler = new MigrationHandler( [ $this->create_mock_migration( '4.2.0' ) ] );
		$handler->register();

		unset( $_GET['page'] );

		$this->assertEmpty( NoticeManager::get_formatted_notices(), 'No notice should be queued on a Kudos admin page.' );
	}

	/**
	 * Test that register() adds needsUpgrade=true to the localization filter
	 * when a non-auto migration is pending.
	 */
	public function test_register_sets_needs_upgrade_in_localization_when_pending(): void {
		update_option( MigrationHandler::SETTING_MIGRATION_HISTORY, [] );

		$handler = new MigrationHandler( [ $this->create_mock_migration( '4.2.0' ) ] );
		$handler->register();

		$data = Localization::get_admin();

		$this->assertTrue( $data['admin']['needsUpgrade'] );
	}

	/**
	 * Test that register() does not add needsUpgrade when no non-auto migrations are pending.
	 */
	public function test_register_does_not_set_needs_upgrade_when_no_manual_pending(): void {
		$handler = new MigrationHandler( [] );
		$handler->register();

		$data = Localization::get_admin();

		$this->assertArrayNotHasKey( 'needsUpgrade', $data['admin'] ?? [] );
	}
}
