<?php
/**
 * SubscriptionRepository tests.
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
 * @covers \IseardMedia\Kudos\Domain\Repository\SubscriptionRepository
 */
class SubscriptionRepositoryTest extends BaseTestCase {

	private SubscriptionRepository $subscription_repository;
	private CampaignRepository $campaign_repository;
	private TransactionRepository $transaction_repository;
	private DonorRepository $donor_repository;

	public function set_up(): void {
		parent::set_up();
		$this->subscription_repository = $this->get_from_container( SubscriptionRepository::class );
		$this->campaign_repository     = $this->get_from_container( CampaignRepository::class );
		$this->transaction_repository  = $this->get_from_container( TransactionRepository::class );
		$this->donor_repository        = $this->get_from_container( DonorRepository::class );
	}

	// -------------------------------------------------------------------------
	// get_transaction()
	// -------------------------------------------------------------------------

	/**
	 * Test that get_transaction() returns the linked transaction.
	 */
	public function test_get_transaction_returns_linked_transaction(): void {
		$sub_id = $this->subscription_repository->insert( new SubscriptionEntity( [ 'value' => 10.00, 'currency' => 'EUR' ] ) );
		$this->transaction_repository->insert( new TransactionEntity( [ 'subscription_id' => $sub_id, 'value' => 10.00, 'currency' => 'EUR' ] ) );

		/** @var SubscriptionEntity $subscription */
		$subscription = $this->subscription_repository->get( $sub_id );
		$transaction  = $this->subscription_repository->get_transaction( $subscription );

		$this->assertInstanceOf( TransactionEntity::class, $transaction );
		$this->assertSame( $sub_id, $transaction->subscription_id );
	}

	/**
	 * Test that get_transaction() returns null when no transaction is linked.
	 */
	public function test_get_transaction_returns_null_when_none_linked(): void {
		$sub_id = $this->subscription_repository->insert( new SubscriptionEntity( [ 'value' => 10.00, 'currency' => 'EUR' ] ) );

		/** @var SubscriptionEntity $subscription */
		$subscription = $this->subscription_repository->get( $sub_id );

		$this->assertNull( $this->subscription_repository->get_transaction( $subscription ) );
	}

	// -------------------------------------------------------------------------
	// get_donor()
	// -------------------------------------------------------------------------

	/**
	 * Test that get_donor() returns the linked donor.
	 */
	public function test_get_donor_returns_linked_donor(): void {
		$donor_id = $this->donor_repository->insert( new DonorEntity( [ 'email' => 'donor@example.com' ] ) );
		$sub_id   = $this->subscription_repository->insert( new SubscriptionEntity( [ 'donor_id' => $donor_id, 'value' => 10.00, 'currency' => 'EUR' ] ) );

		/** @var SubscriptionEntity $subscription */
		$subscription = $this->subscription_repository->get( $sub_id );
		$donor        = $this->subscription_repository->get_donor( $subscription );

		$this->assertInstanceOf( DonorEntity::class, $donor );
		$this->assertSame( $donor_id, $donor->id );
	}

	/**
	 * Test that get_donor() returns null when donor_id is not set.
	 */
	public function test_get_donor_returns_null_when_donor_id_not_set(): void {
		$sub_id = $this->subscription_repository->insert( new SubscriptionEntity( [ 'value' => 10.00, 'currency' => 'EUR' ] ) );

		/** @var SubscriptionEntity $subscription */
		$subscription = $this->subscription_repository->get( $sub_id );

		$this->assertNull( $this->subscription_repository->get_donor( $subscription ) );
	}

	// -------------------------------------------------------------------------
	// get_campaign()
	// -------------------------------------------------------------------------

	/**
	 * Test that get_campaign() returns the campaign when campaign_id is set directly.
	 */
	public function test_get_campaign_returns_campaign_from_campaign_id(): void {
		$campaign_id = $this->campaign_repository->insert( new CampaignEntity( [ 'title' => 'Direct Campaign' ] ) );
		$sub_id      = $this->subscription_repository->insert( new SubscriptionEntity( [ 'campaign_id' => $campaign_id, 'value' => 10.00, 'currency' => 'EUR' ] ) );

		/** @var SubscriptionEntity $subscription */
		$subscription = $this->subscription_repository->get( $sub_id );
		$campaign     = $this->subscription_repository->get_campaign( $subscription );

		$this->assertInstanceOf( CampaignEntity::class, $campaign );
		$this->assertSame( $campaign_id, $campaign->id );
	}

	/**
	 * Test that get_campaign() falls back to the linked transaction's campaign when campaign_id is not set.
	 */
	public function test_get_campaign_falls_back_to_transaction_campaign(): void {
		$campaign_id = $this->campaign_repository->insert( new CampaignEntity( [ 'title' => 'Fallback Campaign' ] ) );
		$sub_id      = $this->subscription_repository->insert( new SubscriptionEntity( [ 'value' => 10.00, 'currency' => 'EUR' ] ) );
		$this->transaction_repository->insert( new TransactionEntity( [ 'subscription_id' => $sub_id, 'campaign_id' => $campaign_id, 'value' => 10.00, 'currency' => 'EUR' ] ) );

		/** @var SubscriptionEntity $subscription */
		$subscription = $this->subscription_repository->get( $sub_id );
		$campaign     = $this->subscription_repository->get_campaign( $subscription );

		$this->assertInstanceOf( CampaignEntity::class, $campaign );
		$this->assertSame( $campaign_id, $campaign->id );
	}

	/**
	 * Test that get_campaign() returns null when neither campaign_id nor a linked transaction is available.
	 */
	public function test_get_campaign_returns_null_when_no_campaign_resolvable(): void {
		$sub_id = $this->subscription_repository->insert( new SubscriptionEntity( [ 'value' => 10.00, 'currency' => 'EUR' ] ) );

		/** @var SubscriptionEntity $subscription */
		$subscription = $this->subscription_repository->get( $sub_id );

		$this->assertNull( $this->subscription_repository->get_campaign( $subscription ) );
	}

	// -------------------------------------------------------------------------
	// Schema / serialization
	// -------------------------------------------------------------------------

	/**
	 * Test that float fields roundtrip correctly through insert and retrieval.
	 */
	public function test_float_value_roundtrips_correctly(): void {
		$sub_id = $this->subscription_repository->insert( new SubscriptionEntity( [ 'value' => 12.50, 'currency' => 'EUR' ] ) );

		/** @var SubscriptionEntity $result */
		$result = $this->subscription_repository->get( $sub_id );

		$this->assertSame( 12.50, $result->value );
	}

	/**
	 * Test that the auto-generated title contains the subscription singular name.
	 */
	public function test_insert_generates_title_with_subscription_label(): void {
		$sub_id = $this->subscription_repository->insert( new SubscriptionEntity( [ 'value' => 5.00, 'currency' => 'EUR' ] ) );

		/** @var SubscriptionEntity $result */
		$result = $this->subscription_repository->get( $sub_id );

		$this->assertStringContainsString( 'Subscription', $result->title );
	}
}