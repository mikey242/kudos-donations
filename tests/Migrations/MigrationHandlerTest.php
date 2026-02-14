<?php
/**
 * MigrationHandler tests.
 */

namespace IseardMedia\Kudos\Tests\Migrations;

use IseardMedia\Kudos\Container\Handler\MigrationHandler;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Container\Handler\MigrationHandler
 */
class MigrationHandlerTest extends BaseTestCase {

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
}
