<?php
/**
 * Version420 migration tests.
 */

namespace IseardMedia\Kudos\Tests\Migrations;

use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Migrations\Version420;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Migrations\Version420
 */
class Version420Test extends BaseTestCase {

	private Version420 $migration;
	private DonorRepository $donor_repo;

	public function set_up(): void {
		parent::set_up();
		$this->migration  = $this->get_from_container( Version420::class );
		$this->donor_repo = $this->get_from_container( DonorRepository::class );
	}

	/**
	 * Test that get_version returns the correct version string.
	 */
	public function test_get_version(): void {
		$this->assertSame( '4.2.0', $this->migration->get_version() );
	}

	/**
	 * Test that get_jobs returns all expected jobs.
	 */
	public function test_get_jobs_returns_all_jobs(): void {
		$jobs = $this->migration->get_jobs();

		$expected_keys = [
			'prepare_tables',
			'donors',
			'campaigns',
			'transactions',
			'subscriptions',
			'backfill_transactions',
			'backfill_remaining_transactions',
			'relink_donors_to_transactions',
			'relink_donors_to_subscriptions',
			'refresh_mollie',
		];

		foreach ( $expected_keys as $key ) {
			$this->assertArrayHasKey( $key, $jobs, "Missing job: $key" );
		}
	}

	/**
	 * Test that prepare_tables and refresh_mollie are marked as non-chunked.
	 */
	public function test_non_chunked_jobs_are_marked(): void {
		$jobs = $this->migration->get_jobs();

		$this->assertFalse( $jobs['prepare_tables']['chunked'] );
		$this->assertFalse( $jobs['refresh_mollie']['chunked'] );
	}

	/**
	 * Test that data migration jobs are marked as chunked.
	 */
	public function test_chunked_jobs_are_marked(): void {
		$jobs         = $this->migration->get_jobs();
		$chunked_keys = [ 'donors', 'campaigns', 'transactions', 'subscriptions' ];

		foreach ( $chunked_keys as $key ) {
			$this->assertTrue( $jobs[ $key ]['chunked'], "Job '$key' should be chunked" );
		}
	}

	/**
	 * Test that get_unmigrated_posts returns post IDs not yet in the repository.
	 */
	public function test_get_unmigrated_posts_returns_unmigrated(): void {
		// Create donor CPTs.
		$post_id_1 = wp_insert_post(
			[
				'post_type'   => 'kudos_donor',
				'post_status' => 'publish',
				'post_title'  => 'Donor 1',
			]
		);
		$post_id_2 = wp_insert_post(
			[
				'post_type'   => 'kudos_donor',
				'post_status' => 'publish',
				'post_title'  => 'Donor 2',
			]
		);

		$unmigrated = $this->migration->get_unmigrated_posts( $this->donor_repo, 'kudos_donor', 50 );

		$this->assertContains( $post_id_1, $unmigrated );
		$this->assertContains( $post_id_2, $unmigrated );
	}

	/**
	 * Test that get_unmigrated_posts excludes already migrated posts.
	 */
	public function test_get_unmigrated_posts_excludes_migrated(): void {
		// Create donor CPTs.
		$post_id_1 = wp_insert_post(
			[
				'post_type'   => 'kudos_donor',
				'post_status' => 'publish',
				'post_title'  => 'Donor 1',
			]
		);
		$post_id_2 = wp_insert_post(
			[
				'post_type'   => 'kudos_donor',
				'post_status' => 'publish',
				'post_title'  => 'Donor 2',
			]
		);

		// Migrate donor 1 into the table.
		$donor = new DonorEntity(
			[
				'wp_post_id' => $post_id_1,
				'title'      => 'Donor 1',
				'email'      => 'donor1@example.com',
			]
		);
		$this->donor_repo->insert( $donor );

		$unmigrated = $this->migration->get_unmigrated_posts( $this->donor_repo, 'kudos_donor', 50 );

		$this->assertNotContains( $post_id_1, $unmigrated );
		$this->assertContains( $post_id_2, $unmigrated );
	}

	/**
	 * Test that get_unmigrated_posts returns empty when all are migrated.
	 */
	public function test_get_unmigrated_posts_returns_empty_when_all_migrated(): void {
		$post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_donor',
				'post_status' => 'publish',
				'post_title'  => 'Donor 1',
			]
		);

		$donor = new DonorEntity(
			[
				'wp_post_id' => $post_id,
				'title'      => 'Donor 1',
				'email'      => 'donor@example.com',
			]
		);
		$this->donor_repo->insert( $donor );

		$unmigrated = $this->migration->get_unmigrated_posts( $this->donor_repo, 'kudos_donor', 50 );

		$this->assertEmpty( $unmigrated );
	}

	/**
	 * Test that get_unmigrated_posts respects the limit parameter.
	 */
	public function test_get_unmigrated_posts_respects_limit(): void {
		for ( $i = 0; $i < 5; $i++ ) {
			wp_insert_post(
				[
					'post_type'   => 'kudos_donor',
					'post_status' => 'publish',
					'post_title'  => "Donor $i",
				]
			);
		}

		$unmigrated = $this->migration->get_unmigrated_posts( $this->donor_repo, 'kudos_donor', 2 );

		$this->assertCount( 2, $unmigrated );
	}

	/**
	 * Test that migrate_donors migrates CPTs to the donors table.
	 */
	public function test_migrate_donors_creates_table_rows(): void {
		$post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_donor',
				'post_status' => 'publish',
				'post_title'  => 'Test Donor',
			]
		);
		update_post_meta( $post_id, 'email', 'test@example.com' );
		update_post_meta( $post_id, 'name', 'Test Donor' );
		update_post_meta( $post_id, 'mode', 'test' );

		$processed = $this->migration->migrate_donors( 50 );

		$this->assertSame( 1, $processed );

		$donor = $this->donor_repo->find_one_by( [ 'wp_post_id' => $post_id ] );
		$this->assertNotNull( $donor );
		$this->assertSame( 'test@example.com', $donor->email );
		$this->assertSame( 'Test Donor', $donor->name );
		$this->assertSame( 'test', $donor->mode );
	}

	/**
	 * Test that migrate_donors is idempotent.
	 */
	public function test_migrate_donors_is_idempotent(): void {
		$post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_donor',
				'post_status' => 'publish',
				'post_title'  => 'Test Donor',
			]
		);
		update_post_meta( $post_id, 'email', 'test@example.com' );

		// First run migrates the donor.
		$first_run = $this->migration->migrate_donors( 50 );
		$this->assertSame( 1, $first_run );

		// Second run finds nothing to migrate.
		$second_run = $this->migration->migrate_donors( 50 );
		$this->assertSame( 0, $second_run );

		// Only one row in the table.
		$all_donors = $this->donor_repo->all();
		$this->assertCount( 1, $all_donors );
	}

	/**
	 * Test that migrate_donors returns 0 when no CPTs exist.
	 */
	public function test_migrate_donors_returns_zero_when_empty(): void {
		$result = $this->migration->migrate_donors( 50 );

		$this->assertSame( 0, $result );
	}
}
