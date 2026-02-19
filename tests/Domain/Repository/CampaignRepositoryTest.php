<?php
/**
 * CampaignRepository tests.
 */

namespace IseardMedia\Kudos\Tests\Domain\Repository;

use IseardMedia\Kudos\Tests\BaseTestCase;
use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;

/**
 * @covers \IseardMedia\Kudos\Domain\Repository\CampaignRepository
 */
class CampaignRepositoryTest extends BaseTestCase {

	private CampaignRepository $campaign_repository;
	private TransactionRepository $transaction_repository;

	public function set_up(): void {
		parent::set_up();
		$this->campaign_repository     = $this->get_from_container( CampaignRepository::class );
		$this->transaction_repository  = $this->get_from_container( TransactionRepository::class );
	}

	// -------------------------------------------------------------------------
	// get_transactions()
	// -------------------------------------------------------------------------

	/**
	 * Test that get_transactions() returns all transactions linked to a campaign.
	 */
	public function test_get_transactions_returns_linked_transactions(): void {
		$campaign_id = $this->campaign_repository->insert( new CampaignEntity( [ 'title' => 'Linked Campaign' ] ) );

		$this->transaction_repository->insert( new TransactionEntity( [ 'campaign_id' => $campaign_id, 'value' => 15.00, 'status' => 'paid', 'currency' => 'EUR' ] ) );
		$this->transaction_repository->insert( new TransactionEntity( [ 'campaign_id' => $campaign_id, 'value' => 30.00, 'status' => 'open', 'currency' => 'EUR' ] ) );

		/** @var CampaignEntity $campaign */
		$campaign     = $this->campaign_repository->get( $campaign_id );
		$transactions = $this->campaign_repository->get_transactions( $campaign );

		$this->assertIsArray( $transactions );
		$this->assertCount( 2, $transactions );
	}

	/**
	 * Test that get_transactions() returns an empty array when no transactions are linked.
	 */
	public function test_get_transactions_returns_empty_for_no_linked_transactions(): void {
		$campaign_id = $this->campaign_repository->insert( new CampaignEntity( [ 'title' => 'Empty Campaign' ] ) );

		/** @var CampaignEntity $campaign */
		$campaign     = $this->campaign_repository->get( $campaign_id );
		$transactions = $this->campaign_repository->get_transactions( $campaign );

		$this->assertIsArray( $transactions );
		$this->assertCount( 0, $transactions );
	}

	/**
	 * Test that get_transactions() returns null when the campaign entity has no ID.
	 */
	public function test_get_transactions_returns_null_when_campaign_has_no_id(): void {
		$campaign = new CampaignEntity( [ 'title' => 'No ID' ] );

		$result = $this->campaign_repository->get_transactions( $campaign );

		$this->assertNull( $result );
	}

	// -------------------------------------------------------------------------
	// get_total()
	// -------------------------------------------------------------------------

	/**
	 * Test that get_total() returns the sum of paid transactions only.
	 */
	public function test_get_total_returns_sum_of_paid_transactions(): void {
		$campaign_id = $this->campaign_repository->insert( new CampaignEntity( [ 'title' => 'Total Campaign' ] ) );

		$this->transaction_repository->insert( new TransactionEntity( [ 'campaign_id' => $campaign_id, 'value' => 20.00, 'status' => 'paid',   'currency' => 'EUR' ] ) );
		$this->transaction_repository->insert( new TransactionEntity( [ 'campaign_id' => $campaign_id, 'value' => 5.00,  'status' => 'open',   'currency' => 'EUR' ] ) );
		$this->transaction_repository->insert( new TransactionEntity( [ 'campaign_id' => $campaign_id, 'value' => 15.00, 'status' => 'paid',   'currency' => 'EUR' ] ) );

		/** @var CampaignEntity $campaign */
		$campaign = $this->campaign_repository->get( $campaign_id );

		$this->assertSame( 35.00, $this->campaign_repository->get_total( $campaign ) );
	}

	/**
	 * Test that get_total() returns 0.0 when there are no transactions at all.
	 */
	public function test_get_total_returns_zero_for_no_transactions(): void {
		$campaign_id = $this->campaign_repository->insert( new CampaignEntity( [ 'title' => 'Zero Campaign' ] ) );

		/** @var CampaignEntity $campaign */
		$campaign = $this->campaign_repository->get( $campaign_id );

		$this->assertSame( 0.0, $this->campaign_repository->get_total( $campaign ) );
	}

	/**
	 * Test that get_total() returns 0.0 when no transactions have a paid status.
	 */
	public function test_get_total_returns_zero_when_no_paid_transactions(): void {
		$campaign_id = $this->campaign_repository->insert( new CampaignEntity( [ 'title' => 'Unpaid Campaign' ] ) );

		$this->transaction_repository->insert( new TransactionEntity( [ 'campaign_id' => $campaign_id, 'value' => 10.00, 'status' => 'open',   'currency' => 'EUR' ] ) );
		$this->transaction_repository->insert( new TransactionEntity( [ 'campaign_id' => $campaign_id, 'value' => 20.00, 'status' => 'failed', 'currency' => 'EUR' ] ) );

		/** @var CampaignEntity $campaign */
		$campaign = $this->campaign_repository->get( $campaign_id );

		$this->assertSame( 0.0, $this->campaign_repository->get_total( $campaign ) );
	}

	// -------------------------------------------------------------------------
	// Schema / serialization
	// -------------------------------------------------------------------------

	/**
	 * Test that OBJECT fields (fixed_amounts) roundtrip correctly as arrays.
	 */
	public function test_fixed_amounts_roundtrips_as_array(): void {
		$amounts     = [ '10', '25', '50', '100' ];
		$campaign_id = $this->campaign_repository->insert( new CampaignEntity( [ 'fixed_amounts' => $amounts ] ) );

		/** @var CampaignEntity $result */
		$result = $this->campaign_repository->get( $campaign_id );

		$this->assertIsArray( $result->fixed_amounts );
		$this->assertSame( $amounts, $result->fixed_amounts );
	}

	/**
	 * Test that OBJECT fields (frequency_options) roundtrip correctly as arrays.
	 */
	public function test_frequency_options_roundtrips_as_array(): void {
		$options     = [ '1 month' => 'Monthly', '12 months' => 'Yearly' ];
		$campaign_id = $this->campaign_repository->insert( new CampaignEntity( [ 'frequency_options' => $options ] ) );

		/** @var CampaignEntity $result */
		$result = $this->campaign_repository->get( $campaign_id );

		$this->assertIsArray( $result->frequency_options );
		$this->assertSame( $options, $result->frequency_options );
	}

	/**
	 * Test that boolean fields roundtrip correctly through insert and retrieval.
	 */
	public function test_boolean_fields_roundtrip_correctly(): void {
		$campaign_id = $this->campaign_repository->insert( new CampaignEntity( [
			'show_goal'       => true,
			'address_enabled' => true,
			'email_enabled'   => false,
		] ) );

		/** @var CampaignEntity $result */
		$result = $this->campaign_repository->get( $campaign_id );

		$this->assertTrue( $result->show_goal );
		$this->assertTrue( $result->address_enabled );
		$this->assertFalse( $result->email_enabled );
	}

	/**
	 * Test that float fields roundtrip correctly through insert and retrieval.
	 */
	public function test_float_fields_roundtrip_correctly(): void {
		$campaign_id = $this->campaign_repository->insert( new CampaignEntity( [
			'goal'             => 1500.50,
			'minimum_donation' => 2.50,
			'maximum_donation' => 999.99,
		] ) );

		/** @var CampaignEntity $result */
		$result = $this->campaign_repository->get( $campaign_id );

		$this->assertSame( 1500.50, $result->goal );
		$this->assertSame( 2.50, $result->minimum_donation );
		$this->assertSame( 999.99, $result->maximum_donation );
	}

	/**
	 * Test that the auto-generated title contains the campaign singular name.
	 */
	public function test_insert_generates_title_with_campaign_label(): void {
		$id = $this->campaign_repository->insert( new CampaignEntity() );

		/** @var CampaignEntity $result */
		$result = $this->campaign_repository->get( $id );
		$this->assertStringContainsString( 'Campaign', $result->title );
	}
}