<?php
/**
 * End-to-end integration test: simulates a 4.1.6 → 4.2.0 upgrade.
 *
 * Creates CPT-based data as it would exist after running version 4.1.6,
 * runs every Version420 migration job in order, then asserts the resulting
 * rows in the new custom tables are correct and fully linked.
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
use IseardMedia\Kudos\Tests\BaseIntegrationTestCase;

/**
 * @covers \IseardMedia\Kudos\Migrations\Version420
 */
class Version420IntegrationTest extends BaseIntegrationTestCase {

	private Version420 $migration;
	private DonorRepository $donor_repo;
	private CampaignRepository $campaign_repo;
	private TransactionRepository $transaction_repo;
	private SubscriptionRepository $subscription_repo;

	// Post IDs of the legacy CPTs.
	private int $donor_post_id;
	private int $campaign_post_id;
	private int $first_tx_post_id;
	private int $recurring_tx_post_id;
	private int $sub_post_id;

	private string $vendor_customer_id = 'cst_abc123';
	private string $vendor_sub_id      = 'sub_xyz789';

	public function set_up(): void {
		parent::set_up();

		// Migration jobs run dbDelta (CREATE TABLE DDL), which causes an implicit
		// MySQL commit and breaks WP_UnitTestCase's transaction rollback. Delete
		// all custom table rows explicitly so each test starts from a clean state.
		global $wpdb;
		foreach ( [ 'kudos_campaigns', 'kudos_donors', 'kudos_transactions', 'kudos_subscriptions' ] as $table ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "DELETE FROM {$wpdb->prefix}{$table}" );
		}

		$this->migration         = $this->get_from_container( Version420::class );
		$this->donor_repo        = $this->get_from_container( DonorRepository::class );
		$this->campaign_repo     = $this->get_from_container( CampaignRepository::class );
		$this->transaction_repo  = $this->get_from_container( TransactionRepository::class );
		$this->subscription_repo = $this->get_from_container( SubscriptionRepository::class );

		$this->seed_legacy_cpts();
	}

	/**
	 * Creates the CPT data that would exist in a 4.1.6 install.
	 */
	private function seed_legacy_cpts(): void {
		// Donor CPT.
		$this->donor_post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_donor',
				'post_status' => 'publish',
				'post_title'  => 'Jane Doe',
			]
		);
		update_post_meta( $this->donor_post_id, 'email', 'jane@example.com' );
		update_post_meta( $this->donor_post_id, 'name', 'Jane Doe' );
		update_post_meta( $this->donor_post_id, 'mode', 'live' );
		update_post_meta( $this->donor_post_id, 'vendor_customer_id', $this->vendor_customer_id );

		// Campaign CPT.
		$this->campaign_post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_campaign',
				'post_status' => 'publish',
				'post_title'  => 'Save the Trees',
			]
		);
		update_post_meta( $this->campaign_post_id, 'currency', 'EUR' );
		update_post_meta( $this->campaign_post_id, 'goal', '1000' );
		update_post_meta( $this->campaign_post_id, 'show_goal', '1' );
		update_post_meta( $this->campaign_post_id, 'donation_type', 'both' );
		update_post_meta( $this->campaign_post_id, 'amount_type', 'open' );
		update_post_meta( $this->campaign_post_id, 'fixed_amounts', '5,10,25,50' );

		// First-payment transaction CPT (the mandate/first charge of a subscription).
		$this->first_tx_post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_transaction',
				'post_status' => 'publish',
				'post_title'  => 'tr_firstpayment',
			]
		);
		update_post_meta( $this->first_tx_post_id, 'value', '25.00' );
		update_post_meta( $this->first_tx_post_id, 'currency', 'EUR' );
		update_post_meta( $this->first_tx_post_id, 'status', 'paid' );
		update_post_meta( $this->first_tx_post_id, 'sequence_type', 'first' );
		update_post_meta( $this->first_tx_post_id, 'mode', 'live' );
		update_post_meta( $this->first_tx_post_id, 'vendor_payment_id', 'tr_abc001' );
		update_post_meta( $this->first_tx_post_id, 'vendor_customer_id', $this->vendor_customer_id );
		update_post_meta( $this->first_tx_post_id, 'donor_id', $this->donor_post_id );
		update_post_meta( $this->first_tx_post_id, 'campaign_id', $this->campaign_post_id );

		// Recurring transaction CPT (a subsequent charge, linked to the subscription only by vendor IDs).
		$this->recurring_tx_post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_transaction',
				'post_status' => 'publish',
				'post_title'  => 'tr_recurring',
			]
		);
		update_post_meta( $this->recurring_tx_post_id, 'value', '25.00' );
		update_post_meta( $this->recurring_tx_post_id, 'currency', 'EUR' );
		update_post_meta( $this->recurring_tx_post_id, 'status', 'paid' );
		update_post_meta( $this->recurring_tx_post_id, 'sequence_type', 'recurring' );
		update_post_meta( $this->recurring_tx_post_id, 'mode', 'live' );
		update_post_meta( $this->recurring_tx_post_id, 'vendor_payment_id', 'tr_abc002' );
		update_post_meta( $this->recurring_tx_post_id, 'vendor_customer_id', $this->vendor_customer_id );
		update_post_meta( $this->recurring_tx_post_id, 'donor_id', $this->donor_post_id );
		update_post_meta( $this->recurring_tx_post_id, 'campaign_id', $this->campaign_post_id );

		// Subscription CPT (transaction_id points to the first-payment transaction).
		$this->sub_post_id = wp_insert_post(
			[
				'post_type'   => 'kudos_subscription',
				'post_status' => 'publish',
				'post_title'  => 'sub_test',
			]
		);
		update_post_meta( $this->sub_post_id, 'value', '25.00' );
		update_post_meta( $this->sub_post_id, 'currency', 'EUR' );
		update_post_meta( $this->sub_post_id, 'frequency', '1 month' );
		update_post_meta( $this->sub_post_id, 'status', 'active' );
		update_post_meta( $this->sub_post_id, 'customer_id', $this->vendor_customer_id ); // v4.1.6 key.
		update_post_meta( $this->sub_post_id, 'vendor_subscription_id', $this->vendor_sub_id );
		update_post_meta( $this->sub_post_id, 'transaction_id', $this->first_tx_post_id );
	}

	/**
	 * Runs all Version420 data jobs in the correct order, draining each chunked
	 * job until it returns 0 before moving to the next.
	 *
	 * Skipped jobs:
	 * - prepare_tables: DDL (CREATE/DROP TABLE) causes an implicit MySQL commit
	 *   that breaks WP_UnitTestCase's transaction rollback. The tables are already
	 *   created correctly by the test bootstrap.
	 * - refresh_mollie: makes live Mollie API calls unavailable in the test env.
	 */
	private function run_full_migration(): void {
		$skip = [ 'prepare_tables', 'refresh_mollie' ];

		foreach ( array_keys( $this->migration->get_jobs() ) as $job ) {
			if ( \in_array( $job, $skip, true ) ) {
				continue;
			}
			do {
				$processed = $this->migration->run( $job );
			} while ( $processed > 0 );
		}
	}

	// -------------------------------------------------------------------------
	// Full end-to-end scenario
	// -------------------------------------------------------------------------

	/**
	 * After a full migration, all entities exist in the new tables.
	 */
	public function test_full_migration_populates_all_tables(): void {
		$this->run_full_migration();

		$this->assertCount( 1, $this->donor_repo->all() );
		$this->assertCount( 1, $this->campaign_repo->all() );
		$this->assertCount( 2, $this->transaction_repo->all() );
		$this->assertCount( 1, $this->subscription_repo->all() );
	}

	/**
	 * Donor fields are mapped correctly from post meta.
	 */
	public function test_full_migration_maps_donor_fields(): void {
		$this->run_full_migration();

		/** @var DonorEntity $donor */
		$donor = $this->donor_repo->find_one_by( [ 'wp_post_id' => $this->donor_post_id ] );

		$this->assertNotNull( $donor );
		$this->assertSame( 'jane@example.com', $donor->email );
		$this->assertSame( 'Jane Doe', $donor->name );
		$this->assertSame( 'live', $donor->mode );
		$this->assertSame( $this->vendor_customer_id, $donor->vendor_customer_id );
	}

	/**
	 * Campaign fields are mapped correctly from post meta.
	 */
	public function test_full_migration_maps_campaign_fields(): void {
		$this->run_full_migration();

		/** @var CampaignEntity $campaign */
		$campaign = $this->campaign_repo->find_one_by( [ 'wp_post_id' => $this->campaign_post_id ] );

		$this->assertNotNull( $campaign );
		$this->assertSame( 'Save the Trees', $campaign->title );
		$this->assertSame( 'EUR', $campaign->currency );
		$this->assertSame( 1000.0, $campaign->goal );
		$this->assertTrue( $campaign->show_goal );
		$this->assertSame( [ '5', '10', '25', '50' ], $campaign->fixed_amounts );
	}

	/**
	 * Transaction donor_id and campaign_id resolve to the new table IDs.
	 */
	public function test_full_migration_maps_transaction_foreign_keys(): void {
		$this->run_full_migration();

		/** @var DonorEntity $donor */
		$donor = $this->donor_repo->find_one_by( [ 'wp_post_id' => $this->donor_post_id ] );
		/** @var CampaignEntity $campaign */
		$campaign = $this->campaign_repo->find_one_by( [ 'wp_post_id' => $this->campaign_post_id ] );

		/** @var TransactionEntity $transaction */
		$transaction = $this->transaction_repo->find_one_by( [ 'wp_post_id' => $this->first_tx_post_id ] );

		$this->assertNotNull( $transaction );
		$this->assertSame( $donor->id, $transaction->donor_id );
		$this->assertSame( $campaign->id, $transaction->campaign_id );
		$this->assertSame( 'tr_abc001', $transaction->vendor_payment_id );
		$this->assertSame( 'first', $transaction->sequence_type );
	}

	/**
	 * Subscription vendor_customer_id is read from the legacy 'customer_id' meta key.
	 */
	public function test_full_migration_maps_subscription_customer_id(): void {
		$this->run_full_migration();

		/** @var SubscriptionEntity $subscription */
		$subscription = $this->subscription_repo->find_one_by( [ 'wp_post_id' => $this->sub_post_id ] );

		$this->assertNotNull( $subscription );
		$this->assertSame( $this->vendor_customer_id, $subscription->vendor_customer_id );
		$this->assertSame( $this->vendor_sub_id, $subscription->vendor_subscription_id );
		$this->assertSame( 'active', $subscription->status );
	}

	/**
	 * The first-payment transaction has subscription_id populated after backfill.
	 */
	public function test_full_migration_links_first_transaction_to_subscription(): void {
		$this->run_full_migration();

		/** @var SubscriptionEntity $subscription */
		$subscription = $this->subscription_repo->find_one_by( [ 'wp_post_id' => $this->sub_post_id ] );
		/** @var TransactionEntity $transaction */
		$transaction = $this->transaction_repo->find_one_by( [ 'wp_post_id' => $this->first_tx_post_id ] );

		$this->assertNotNull( $subscription );
		$this->assertNotNull( $transaction );
		$this->assertSame( $subscription->id, $transaction->subscription_id );
	}

	/**
	 * The recurring transaction has subscription_id populated after backfill_remaining.
	 */
	public function test_full_migration_links_recurring_transaction_to_subscription(): void {
		$this->run_full_migration();

		/** @var SubscriptionEntity $subscription */
		$subscription = $this->subscription_repo->find_one_by( [ 'wp_post_id' => $this->sub_post_id ] );
		/** @var TransactionEntity $recurring */
		$recurring = $this->transaction_repo->find_one_by( [ 'wp_post_id' => $this->recurring_tx_post_id ] );

		$this->assertNotNull( $subscription );
		$this->assertNotNull( $recurring );
		$this->assertSame( $subscription->id, $recurring->subscription_id );
	}

	/**
	 * After migration, all transactions have the correct donor_id (re-link step).
	 */
	public function test_full_migration_relinks_donors_to_transactions(): void {
		$this->run_full_migration();

		/** @var DonorEntity $donor */
		$donor = $this->donor_repo->find_one_by( [ 'wp_post_id' => $this->donor_post_id ] );
		$transactions = $this->transaction_repo->all();

		foreach ( $transactions as $tx ) {
			$this->assertSame( $donor->id, $tx->donor_id, "Transaction {$tx->id} should be linked to donor {$donor->id}" );
		}
	}

	/**
	 * Running the full migration twice produces the same result (idempotent).
	 */
	public function test_full_migration_is_idempotent(): void {
		$this->run_full_migration();
		$this->run_full_migration();

		$this->assertCount( 1, $this->donor_repo->all() );
		$this->assertCount( 1, $this->campaign_repo->all() );
		$this->assertCount( 2, $this->transaction_repo->all() );
		$this->assertCount( 1, $this->subscription_repo->all() );
	}
}
