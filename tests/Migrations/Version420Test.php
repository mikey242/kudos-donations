<?php
/**
 * Version420 migration tests.
 */

namespace IseardMedia\Kudos\Tests\Migrations;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Domain\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Migrations\Version420;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Migrations\Version420
 */
class Version420Test extends BaseTestCase {

	private Version420 $migration;
	private CampaignRepository $campaign_repo;
	private DonorRepository $donor_repo;
	private TransactionRepository $transaction_repo;
	private SubscriptionRepository $subscription_repo;

	public function set_up(): void {
		parent::set_up();
		$this->migration        = $this->get_from_container( Version420::class );
		$this->campaign_repo    = $this->get_from_container( CampaignRepository::class );
		$this->donor_repo       = $this->get_from_container( DonorRepository::class );
		$this->transaction_repo = $this->get_from_container( TransactionRepository::class );
		$this->subscription_repo = $this->get_from_container( SubscriptionRepository::class );
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

	/**
	 * Test that migrate_campaigns creates a row in the campaigns table.
	 */
	public function test_migrate_campaigns_creates_table_rows(): void {
		$post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_campaign',
				'post_status' => 'publish',
				'post_title'  => 'Test Campaign',
			]
		);
		update_post_meta( $post_id, 'currency', 'EUR' );
		update_post_meta( $post_id, 'goal', '500' );
		update_post_meta( $post_id, 'show_goal', '1' );

		$processed = $this->migration->migrate_campaigns( 50 );

		$this->assertSame( 1, $processed );

		/** @var CampaignEntity|null $campaign */
		$campaign = $this->campaign_repo->find_one_by( [ 'wp_post_id' => $post_id ] );
		$this->assertNotNull( $campaign );
		$this->assertSame( 'EUR', $campaign->currency );
		$this->assertSame( 500.0, $campaign->goal );
		$this->assertTrue( $campaign->show_goal );
	}

	/**
	 * Test that migrate_campaigns is idempotent.
	 */
	public function test_migrate_campaigns_is_idempotent(): void {
		$post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_campaign',
				'post_status' => 'publish',
				'post_title'  => 'Test Campaign',
			]
		);

		$this->assertSame( 1, $this->migration->migrate_campaigns( 50 ) );
		$this->assertSame( 0, $this->migration->migrate_campaigns( 50 ) );
		$this->assertCount( 1, $this->campaign_repo->all() );
	}

	/**
	 * Test that migrate_campaigns defaults currency to EUR when not set.
	 */
	public function test_migrate_campaigns_defaults_currency_to_eur(): void {
		$post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_campaign',
				'post_status' => 'publish',
				'post_title'  => 'No Currency',
			]
		);

		$this->migration->migrate_campaigns( 50 );

		/** @var CampaignEntity|null $campaign */
		$campaign = $this->campaign_repo->find_one_by( [ 'wp_post_id' => $post_id ] );
		$this->assertSame( 'EUR', $campaign->currency );
	}

	/**
	 * Test that migrate_transactions creates a row in the transactions table.
	 */
	public function test_migrate_transactions_creates_table_rows(): void {
		$post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_transaction',
				'post_status' => 'publish',
				'post_title'  => 'tr_test123',
			]
		);
		update_post_meta( $post_id, 'value', '25.00' );
		update_post_meta( $post_id, 'currency', 'EUR' );
		update_post_meta( $post_id, 'status', 'paid' );
		update_post_meta( $post_id, 'sequence_type', 'oneoff' );
		update_post_meta( $post_id, 'vendor_payment_id', 'tr_abc123' );

		$processed = $this->migration->migrate_transactions( 50 );

		$this->assertSame( 1, $processed );

		/** @var TransactionEntity|null $transaction */
		$transaction = $this->transaction_repo->find_one_by( [ 'wp_post_id' => $post_id ] );
		$this->assertNotNull( $transaction );
		$this->assertSame( 25.0, $transaction->value );
		$this->assertSame( 'EUR', $transaction->currency );
		$this->assertSame( 'paid', $transaction->status );
		$this->assertSame( 'tr_abc123', $transaction->vendor_payment_id );
	}

	/**
	 * Test that migrate_transactions maps donor and campaign IDs to new table IDs.
	 */
	public function test_migrate_transactions_maps_donor_and_campaign_ids(): void {
		$donor_post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_donor',
				'post_status' => 'publish',
				'post_title'  => 'Donor',
			]
		);
		$campaign_post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_campaign',
				'post_status' => 'publish',
				'post_title'  => 'Campaign',
			]
		);

		// Migrate donor and campaign first so the maps are populated.
		$this->migration->migrate_donors( 50 );
		$this->migration->migrate_campaigns( 50 );

		$donor    = $this->donor_repo->find_one_by( [ 'wp_post_id' => $donor_post_id ] );
		$campaign = $this->campaign_repo->find_one_by( [ 'wp_post_id' => $campaign_post_id ] );

		$tx_post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_transaction',
				'post_status' => 'publish',
				'post_title'  => 'Transaction',
			]
		);
		update_post_meta( $tx_post_id, 'value', '10.00' );
		update_post_meta( $tx_post_id, 'currency', 'EUR' );
		update_post_meta( $tx_post_id, 'status', 'paid' );
		update_post_meta( $tx_post_id, 'donor_id', $donor_post_id );
		update_post_meta( $tx_post_id, 'campaign_id', $campaign_post_id );

		$this->migration->migrate_transactions( 50 );

		/** @var TransactionEntity|null $transaction */
		$transaction = $this->transaction_repo->find_one_by( [ 'wp_post_id' => $tx_post_id ] );
		$this->assertNotNull( $transaction );
		$this->assertSame( $donor->id, $transaction->donor_id );
		$this->assertSame( $campaign->id, $transaction->campaign_id );
	}

	/**
	 * Test that migrate_transactions is idempotent.
	 */
	public function test_migrate_transactions_is_idempotent(): void {
		$post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_transaction',
				'post_status' => 'publish',
				'post_title'  => 'Transaction',
			]
		);
		update_post_meta( $post_id, 'value', '10.00' );
		update_post_meta( $post_id, 'currency', 'EUR' );
		update_post_meta( $post_id, 'status', 'paid' );

		$this->assertSame( 1, $this->migration->migrate_transactions( 50 ) );
		$this->assertSame( 0, $this->migration->migrate_transactions( 50 ) );
		$this->assertCount( 1, $this->transaction_repo->all() );
	}

	/**
	 * Test that migrate_subscriptions creates a row in the subscriptions table.
	 */
	public function test_migrate_subscriptions_creates_table_rows(): void {
		$sub_post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_subscription',
				'post_status' => 'publish',
				'post_title'  => 'sub_test123',
			]
		);
		update_post_meta( $sub_post_id, 'value', '10.00' );
		update_post_meta( $sub_post_id, 'currency', 'EUR' );
		update_post_meta( $sub_post_id, 'frequency', '1 month' );
		update_post_meta( $sub_post_id, 'status', 'active' );
		update_post_meta( $sub_post_id, 'customer_id', 'cst_abc123' );
		update_post_meta( $sub_post_id, 'vendor_subscription_id', 'sub_abc123' );

		$processed = $this->migration->migrate_subscriptions( 50 );

		$this->assertSame( 1, $processed );

		/** @var SubscriptionEntity|null $subscription */
		$subscription = $this->subscription_repo->find_one_by( [ 'wp_post_id' => $sub_post_id ] );
		$this->assertNotNull( $subscription );
		$this->assertSame( 10.0, $subscription->value );
		$this->assertSame( 'EUR', $subscription->currency );
		$this->assertSame( 'active', $subscription->status );
		$this->assertSame( 'cst_abc123', $subscription->vendor_customer_id );
		$this->assertSame( 'sub_abc123', $subscription->vendor_subscription_id );
	}

	/**
	 * Test that migrate_subscriptions reads vendor_customer_id from the 'customer_id' meta key.
	 */
	public function test_migrate_subscriptions_maps_customer_id_meta(): void {
		$sub_post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_subscription',
				'post_status' => 'publish',
				'post_title'  => 'Sub',
			]
		);
		// v4.1.6 stored this as 'customer_id', not 'vendor_customer_id'.
		update_post_meta( $sub_post_id, 'customer_id', 'cst_legacy' );
		update_post_meta( $sub_post_id, 'currency', 'EUR' );
		update_post_meta( $sub_post_id, 'value', '5.00' );

		$this->migration->migrate_subscriptions( 50 );

		/** @var SubscriptionEntity|null $subscription */
		$subscription = $this->subscription_repo->find_one_by( [ 'wp_post_id' => $sub_post_id ] );
		$this->assertNotNull( $subscription );
		$this->assertSame( 'cst_legacy', $subscription->vendor_customer_id );
	}

	/**
	 * Test that migrate_subscriptions is idempotent.
	 */
	public function test_migrate_subscriptions_is_idempotent(): void {
		$post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_subscription',
				'post_status' => 'publish',
				'post_title'  => 'Sub',
			]
		);
		update_post_meta( $post_id, 'value', '10.00' );
		update_post_meta( $post_id, 'currency', 'EUR' );
		update_post_meta( $post_id, 'frequency', '1 month' );
		update_post_meta( $post_id, 'status', 'active' );

		$this->assertSame( 1, $this->migration->migrate_subscriptions( 50 ) );
		$this->assertSame( 0, $this->migration->migrate_subscriptions( 50 ) );
		$this->assertCount( 1, $this->subscription_repo->all() );
	}

	/**
	 * Test that backfill links a first-payment transaction to its subscription.
	 */
	public function test_backfill_transactions_links_subscription_id(): void {
		// Create the subscription CPT with transaction_id meta.
		$tx_post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_transaction',
				'post_status' => 'publish',
				'post_title'  => 'First Payment',
			]
		);
		$sub_post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_subscription',
				'post_status' => 'publish',
				'post_title'  => 'Sub',
			]
		);
		update_post_meta( $sub_post_id, 'transaction_id', $tx_post_id );

		// Insert migrated rows without subscription_id set.
		$tx_id  = $this->transaction_repo->insert(
			new TransactionEntity(
				[
					'wp_post_id'    => $tx_post_id,
					'value'         => 10.0,
					'currency'      => 'EUR',
					'sequence_type' => 'first',
				]
			)
		);
		$sub_id = $this->subscription_repo->insert(
			new SubscriptionEntity(
				[
					'wp_post_id' => $sub_post_id,
					'value'      => 10.0,
					'currency'   => 'EUR',
					'frequency'  => '1 month',
					'status'     => 'active',
				]
			)
		);

		$processed = $this->migration->backfill_transactions_from_subscription( 50 );

		$this->assertSame( 1, $processed );

		/** @var TransactionEntity|null $transaction */
		$transaction = $this->transaction_repo->get( $tx_id );
		$this->assertSame( $sub_id, $transaction->subscription_id );
	}

	/**
	 * Test that backfill returns 0 when no unlinked first-payment transactions exist.
	 */
	public function test_backfill_transactions_returns_zero_when_nothing_to_do(): void {
		// A transaction that is already linked.
		$sub_id = $this->subscription_repo->insert(
			new SubscriptionEntity(
				[
					'value'     => 10.0,
					'currency'  => 'EUR',
					'frequency' => '1 month',
					'status'    => 'active',
				]
			)
		);
		$this->transaction_repo->insert(
			new TransactionEntity(
				[
					'value'           => 10.0,
					'currency'        => 'EUR',
					'sequence_type'   => 'first',
					'subscription_id' => $sub_id,
				]
			)
		);

		$processed = $this->migration->backfill_transactions_from_subscription( 50 );

		$this->assertSame( 0, $processed );
	}

	/**
	 * Test that backfill only processes first-payment transactions, not recurring ones.
	 */
	public function test_backfill_transactions_ignores_recurring_sequence_type(): void {
		$tx_post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_transaction',
				'post_status' => 'publish',
				'post_title'  => 'Recurring Payment',
			]
		);
		// Insert as 'recurring', not 'first'.
		$this->transaction_repo->insert(
			new TransactionEntity(
				[
					'wp_post_id'    => $tx_post_id,
					'value'         => 10.0,
					'currency'      => 'EUR',
					'sequence_type' => 'recurring',
				]
			)
		);

		$processed = $this->migration->backfill_transactions_from_subscription( 50 );

		$this->assertSame( 0, $processed );
	}
}
