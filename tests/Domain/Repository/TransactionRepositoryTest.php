<?php
/**
 * TransactionRepository tests.
 */

namespace IseardMedia\Kudos\Tests\Domain\Repository;

use IseardMedia\Kudos\Tests\BaseTestCase;
use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Domain\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;

/**
 * @covers \IseardMedia\Kudos\Domain\Repository\TransactionRepository
 */
class TransactionRepositoryTest extends BaseTestCase {

	private TransactionRepository $transaction_repository;
	private CampaignRepository $campaign_repository;
	private DonorRepository $donor_repository;
	private SubscriptionRepository $subscription_repository;

	public function set_up(): void {
		parent::set_up();
		$this->transaction_repository  = $this->get_from_container( TransactionRepository::class );
		$this->campaign_repository     = $this->get_from_container( CampaignRepository::class );
		$this->donor_repository        = $this->get_from_container( DonorRepository::class );
		$this->subscription_repository = $this->get_from_container( SubscriptionRepository::class );
	}

	// -------------------------------------------------------------------------
	// get_donor()
	// -------------------------------------------------------------------------

	/**
	 * Test that get_donor() returns the linked donor.
	 */
	public function test_get_donor_returns_linked_donor(): void {
		$donor_id = $this->donor_repository->insert( new DonorEntity( [ 'email' => 'donor@example.com' ] ) );
		$tx_id    = $this->transaction_repository->insert( new TransactionEntity( [ 'donor_id' => $donor_id, 'value' => 10.00, 'currency' => 'EUR' ] ) );

		/** @var TransactionEntity $transaction */
		$transaction = $this->transaction_repository->get( $tx_id );
		$donor       = $this->transaction_repository->get_donor( $transaction );

		$this->assertInstanceOf( DonorEntity::class, $donor );
		$this->assertSame( $donor_id, $donor->id );
	}

	/**
	 * Test that get_donor() returns null when donor_id is not set.
	 */
	public function test_get_donor_returns_null_when_donor_id_not_set(): void {
		$tx_id = $this->transaction_repository->insert( new TransactionEntity( [ 'value' => 10.00, 'currency' => 'EUR' ] ) );

		/** @var TransactionEntity $transaction */
		$transaction = $this->transaction_repository->get( $tx_id );

		$this->assertNull( $this->transaction_repository->get_donor( $transaction ) );
	}

	// -------------------------------------------------------------------------
	// get_campaign()
	// -------------------------------------------------------------------------

	/**
	 * Test that get_campaign() returns the linked campaign.
	 */
	public function test_get_campaign_returns_linked_campaign(): void {
		$campaign_id = $this->campaign_repository->insert( new CampaignEntity( [ 'title' => 'Test Campaign' ] ) );
		$tx_id       = $this->transaction_repository->insert( new TransactionEntity( [ 'campaign_id' => $campaign_id, 'value' => 10.00, 'currency' => 'EUR' ] ) );

		/** @var TransactionEntity $transaction */
		$transaction = $this->transaction_repository->get( $tx_id );
		$campaign    = $this->transaction_repository->get_campaign( $transaction );

		$this->assertInstanceOf( CampaignEntity::class, $campaign );
		$this->assertSame( $campaign_id, $campaign->id );
	}

	/**
	 * Test that get_campaign() returns null when campaign_id is not set.
	 */
	public function test_get_campaign_returns_null_when_campaign_id_not_set(): void {
		$tx_id = $this->transaction_repository->insert( new TransactionEntity( [ 'value' => 10.00, 'currency' => 'EUR' ] ) );

		/** @var TransactionEntity $transaction */
		$transaction = $this->transaction_repository->get( $tx_id );

		$this->assertNull( $this->transaction_repository->get_campaign( $transaction ) );
	}

	// -------------------------------------------------------------------------
	// get_subscription()
	// -------------------------------------------------------------------------

	/**
	 * Test that get_subscription() returns the linked subscription.
	 */
	public function test_get_subscription_returns_linked_subscription(): void {
		$sub_id = $this->subscription_repository->insert( new SubscriptionEntity( [ 'value' => 10.00, 'currency' => 'EUR' ] ) );
		$tx_id  = $this->transaction_repository->insert( new TransactionEntity( [ 'subscription_id' => $sub_id, 'value' => 10.00, 'currency' => 'EUR' ] ) );

		/** @var TransactionEntity $transaction */
		$transaction  = $this->transaction_repository->get( $tx_id );
		$subscription = $this->transaction_repository->get_subscription( $transaction );

		$this->assertInstanceOf( SubscriptionEntity::class, $subscription );
		$this->assertSame( $sub_id, $subscription->id );
	}

	/**
	 * Test that get_subscription() returns null when subscription_id is not set.
	 */
	public function test_get_subscription_returns_null_when_subscription_id_not_set(): void {
		$tx_id = $this->transaction_repository->insert( new TransactionEntity( [ 'value' => 10.00, 'currency' => 'EUR' ] ) );

		/** @var TransactionEntity $transaction */
		$transaction = $this->transaction_repository->get( $tx_id );

		$this->assertNull( $this->transaction_repository->get_subscription( $transaction ) );
	}

	// -------------------------------------------------------------------------
	// get_orphan_ids()
	// -------------------------------------------------------------------------

	/**
	 * Test that get_orphan_ids() returns an empty array when all transactions have valid campaigns.
	 */
	public function test_get_orphan_ids_returns_empty_when_all_campaigns_valid(): void {
		$campaign_id = $this->campaign_repository->insert( new CampaignEntity( [ 'title' => 'Valid Campaign' ] ) );
		$this->transaction_repository->insert( new TransactionEntity( [ 'campaign_id' => $campaign_id, 'value' => 10.00, 'currency' => 'EUR' ] ) );

		$this->assertSame( [], $this->transaction_repository->get_orphan_ids() );
	}

	/**
	 * Test that get_orphan_ids() returns the ID of a transaction with no campaign_id.
	 */
	public function test_get_orphan_ids_includes_transaction_with_null_campaign(): void {
		$tx_id = $this->transaction_repository->insert( new TransactionEntity( [ 'value' => 10.00, 'currency' => 'EUR' ] ) );

		$this->assertContains( $tx_id, $this->transaction_repository->get_orphan_ids() );
	}

	/**
	 * Test that get_orphan_ids() returns the ID of a transaction whose campaign no longer exists.
	 */
	public function test_get_orphan_ids_includes_transaction_with_missing_campaign(): void {
		$tx_id = $this->transaction_repository->insert( new TransactionEntity( [ 'campaign_id' => 999999, 'value' => 10.00, 'currency' => 'EUR' ] ) );

		$this->assertContains( $tx_id, $this->transaction_repository->get_orphan_ids() );
	}

	// -------------------------------------------------------------------------
	// Schema / serialization
	// -------------------------------------------------------------------------

	/**
	 * Test that float fields roundtrip correctly through insert and retrieval.
	 */
	public function test_float_value_roundtrips_correctly(): void {
		$tx_id = $this->transaction_repository->insert( new TransactionEntity( [ 'value' => 49.99, 'currency' => 'EUR' ] ) );

		/** @var TransactionEntity $result */
		$result = $this->transaction_repository->get( $tx_id );

		$this->assertSame( 49.99, $result->value );
	}

	/**
	 * Test that the auto-generated title contains the transaction singular name.
	 */
	public function test_insert_generates_title_with_transaction_label(): void {
		$tx_id = $this->transaction_repository->insert( new TransactionEntity( [ 'value' => 10.00, 'currency' => 'EUR' ] ) );

		/** @var TransactionEntity $result */
		$result = $this->transaction_repository->get( $tx_id );

		$this->assertStringContainsString( 'Transaction', $result->title );
	}
}