<?php
/**
 * BaseMigration tests.
 */

namespace IseardMedia\Kudos\Tests\Migrations;

use IseardMedia\Kudos\Migrations\BaseMigration;
use IseardMedia\Kudos\Tests\BaseTestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \IseardMedia\Kudos\Migrations\BaseMigration
 */
class BaseMigrationTest extends BaseTestCase {

	private LoggerInterface $logger;

	public function set_up(): void {
		parent::set_up();
		$this->logger = $this->createMock( LoggerInterface::class );
	}

	/**
	 * Create a concrete migration instance for testing.
	 *
	 * @param array $jobs The jobs to return from get_jobs().
	 */
	private function create_migration( array $jobs ): BaseMigration {
		$migration = new class( $jobs ) extends BaseMigration {
			protected string $version = '1.0.0';
			private array $test_jobs;

			public function __construct( array $jobs ) {
				$this->test_jobs = $jobs;
			}

			public function get_jobs(): array {
				return $this->test_jobs;
			}
		};

		$migration->setLogger( $this->logger );

		return $migration;
	}

	/**
	 * Test that a chunked job returns the number of processed items.
	 */
	public function test_run_chunked_job_returns_processed_count(): void {
		$migration = $this->create_migration(
			[
				'my_job' => [
					'callback' => fn( int $limit ) => 25,
					'chunked'  => true,
					'label'    => 'Test job',
				],
			]
		);

		$result = $migration->run( 'my_job' );

		$this->assertSame( 25, $result );
	}

	/**
	 * Test that a chunked job returning 0 signals completion.
	 */
	public function test_run_chunked_job_returns_zero_when_complete(): void {
		$migration = $this->create_migration(
			[
				'my_job' => [
					'callback' => fn( int $limit ) => 0,
					'chunked'  => true,
					'label'    => 'Test job',
				],
			]
		);

		$result = $migration->run( 'my_job' );

		$this->assertSame( 0, $result );
	}

	/**
	 * Test that a chunked job receives the limit parameter.
	 */
	public function test_run_chunked_job_passes_limit(): void {
		$received_limit = null;

		$migration = $this->create_migration(
			[
				'my_job' => [
					'callback' => function ( int $limit ) use ( &$received_limit ) {
						$received_limit = $limit;
						return 0;
					},
					'chunked'  => true,
					'label'    => 'Test job',
				],
			]
		);

		$migration->run( 'my_job' );

		$this->assertSame( 50, $received_limit );
	}

	/**
	 * Test that a non-chunked job returns 0 immediately.
	 */
	public function test_run_non_chunked_job_returns_zero(): void {
		$called = false;

		$migration = $this->create_migration(
			[
				'my_job' => [
					'callback' => function () use ( &$called ) {
						$called = true;
					},
					'chunked'  => false,
					'label'    => 'Test job',
				],
			]
		);

		$result = $migration->run( 'my_job' );

		$this->assertTrue( $called );
		$this->assertSame( 0, $result );
	}

	/**
	 * Test that running an undefined job returns 0.
	 */
	public function test_run_undefined_job_returns_zero(): void {
		$migration = $this->create_migration( [] );

		$result = $migration->run( 'nonexistent' );

		$this->assertSame( 0, $result );
	}

	/**
	 * Test that an exception in a chunked job is caught and returns 0.
	 */
	public function test_run_catches_exception_and_returns_zero(): void {
		$migration = $this->create_migration(
			[
				'my_job' => [
					'callback' => function ( int $limit ) {
						throw new \RuntimeException( 'Something went wrong' );
					},
					'chunked'  => true,
					'label'    => 'Failing job',
				],
			]
		);

		$result = $migration->run( 'my_job' );

		$this->assertSame( 0, $result );
	}

	/**
	 * Test that an exception in a non-chunked job is caught and returns 0.
	 */
	public function test_run_non_chunked_catches_exception(): void {
		$migration = $this->create_migration(
			[
				'my_job' => [
					'callback' => function () {
						throw new \RuntimeException( 'Fail' );
					},
					'chunked'  => false,
					'label'    => 'Failing job',
				],
			]
		);

		$result = $migration->run( 'my_job' );

		$this->assertSame( 0, $result );
	}

	/**
	 * Test that chunked defaults to true when not specified.
	 */
	public function test_run_defaults_to_chunked(): void {
		$received_limit = null;

		$migration = $this->create_migration(
			[
				'my_job' => [
					'callback' => function ( int $limit ) use ( &$received_limit ) {
						$received_limit = $limit;
						return 10;
					},
					'label'    => 'Default chunked',
				],
			]
		);

		$result = $migration->run( 'my_job' );

		$this->assertSame( 50, $received_limit );
		$this->assertSame( 10, $result );
	}

	/**
	 * Test that get_version returns the version string.
	 */
	public function test_get_version(): void {
		$migration = $this->create_migration( [] );

		$this->assertSame( '1.0.0', $migration->get_version() );
	}

	/**
	 * Test that the job() helper produces the correct structure.
	 */
	public function test_job_helper_creates_correct_structure(): void {
		$migration = new class() extends BaseMigration {
			protected string $version = '1.0.0';

			public function get_jobs(): array {
				return [
					'chunked_job'     => $this->job( fn( int $limit ) => 0, 'Chunked' ),
					'non_chunked_job' => $this->job( fn() => null, 'Non-chunked', false ),
				];
			}
		};

		$jobs = $migration->get_jobs();

		$this->assertTrue( $jobs['chunked_job']['chunked'] );
		$this->assertSame( 'Chunked', $jobs['chunked_job']['label'] );

		$this->assertFalse( $jobs['non_chunked_job']['chunked'] );
		$this->assertSame( 'Non-chunked', $jobs['non_chunked_job']['label'] );
	}
}
