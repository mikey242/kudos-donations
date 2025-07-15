<?php
/**
 * CampaignRepository tests.
 */

namespace Repository;

use BaseTestCase;
use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Domain\Schema\TransactionSchema;

/**
 * @covers \IseardMedia\Kudos\Domain\Repository\CampaignRepository
 */
class CampaignRepositoryTest extends BaseTestCase {

	private CampaignRepository $campaign_repository;

	public function set_up(): void {
		parent::set_up();
		$this->campaign_repository = $this->get_repository(CampaignRepository::class);
	}

	/**
	 * Test that campaign is created and returned.
	 */
	public function test_save_creates_campaign(): void {
		$campaign = new CampaignEntity([ 'title' => 'Test Campaign']);
		$id = $this->campaign_repository->insert($campaign);

		$this->assertIsInt($id);
		$this->assertGreaterThan(0, $id);
	}

	/**
	 * Test that campaign is found by id.
	 */
	public function test_find_returns_campaign_by_id(): void {
		$campaign = new CampaignEntity([ 'title' => 'Find Me' ]);
		$id = $this->campaign_repository->insert($campaign);

		/** @var CampaignEntity $campaign */
		$campaign = $this->campaign_repository->get($id);

		$this->assertNotNull($campaign);
		$this->assertSame('Find Me', $campaign->title);
	}

	/**
	 * Test that all() returns all campaigns.
	 */
	public function test_all_returns_all_campaigns(): void {
		$campaign_1 = new CampaignEntity(['title' => 'One']);
		$campaign_2 = new CampaignEntity(['title' => 'Two']);

		$this->campaign_repository->insert($campaign_1);
		$this->campaign_repository->insert($campaign_2);

		$all = $this->campaign_repository->all();

		$this->assertIsArray($all);
		$this->assertCount(2, $all);
	}

	/**
	 * Test that save() updates existing campaign when ID is provided.
	 */
	public function test_save_updates_existing_campaign(): void {
		$campaign = new CampaignEntity([ 'title' => 'Original']);
		$id = $this->campaign_repository->insert($campaign);

		$campaign->title = 'Updated';
		$campaign->id = $id;
		$this->campaign_repository->update($campaign);

		$updated = $this->campaign_repository->get($id);

		$this->assertSame('Updated', $updated->title);
	}

	/**
	 * Test that find() returns null for invalid ID.
	 */
	public function test_find_returns_null_for_invalid_id(): void {
		$campaign = $this->campaign_repository->get(99999);
		$this->assertNull($campaign);
	}

	/**
	 * Test that find_by() returns expected campaign.
	 */
	public function test_find_by_returns_matching_campaign(): void {
		$campaign = new CampaignEntity([ 'title' => 'Special Campaign']);
		$this->campaign_repository->insert($campaign);

		$results = $this->campaign_repository->find_by([
			'title' => 'Special Campaign'
		]);

		$this->assertCount(1, $results);
		$this->assertSame('Special Campaign', $results[0]->title);
	}

	/**
	 * Test that find_by() returns empty array when no match.
	 */
	public function test_find_by_returns_empty_array_for_no_match(): void {
		$results = $this->campaign_repository->find_by([
			'title' => 'Nonexistent'
		]);

		$this->assertIsArray($results);
		$this->assertCount(0, $results);
	}

	/**
	 * Test delete() removes campaign from database.
	 */
	public function test_delete_removes_campaign(): void {
		$campaign = new CampaignEntity(['title' => 'To Delete']);
		$id = $this->campaign_repository->insert($campaign);

		$deleted = $this->campaign_repository->delete($id);
		$this->assertTrue($deleted);

		$campaign = $this->campaign_repository->get($id);
		$this->assertNull($campaign);
	}

	/**
	 * Test that get_transactions() returns linked transactions.
	 */
	public function test_get_transactions_returns_linked_transactions(): void {
		$campaign = new CampaignEntity([ 'title' => 'Linked Campaign']);
		$campaign_id = $this->campaign_repository->insert($campaign);

		$transaction_repo = new TransactionRepository($this->wpdb, new TransactionSchema());

		// Create 2 transactions linked to the campaign
		$transaction_1 = new TransactionEntity([
			'campaign_id' => $campaign_id,
			'value'       => 15.00,
			'status'      => 'paid',
			'currency'    => 'EUR'
		]);

		$transaction_2 = new TransactionEntity([
			'campaign_id' => $campaign_id,
			'value'       => 30.00,
			'status'      => 'open',
			'currency'    => 'EUR'
		]);

		$transaction_repo->insert($transaction_1);
		$transaction_repo->insert($transaction_2);

		$campaign = $this->campaign_repository->get($campaign_id);
		$transactions = $this->campaign_repository->get_transactions($campaign);

		$this->assertIsArray($transactions);
		$this->assertCount(2, $transactions);
	}

	/**
	 * Test that get_total() returns the sum of 'paid' transactions.
	 */
	public function test_get_total_returns_sum_of_paid_transactions(): void {
		$campaign = new CampaignEntity([ 'title' => 'Total Campaign']);
		$campaign_id = $this->campaign_repository->insert($campaign);
		$transaction_repo = new TransactionRepository($this->wpdb, new TransactionSchema());

		$transaction_1 = new TransactionEntity([
			'campaign_id' => $campaign_id,
			'value'       => 20.00,
			'status'      => 'paid',
			'currency'    => 'EUR'
		]);

		$transaction_2 = new TransactionEntity([
			'campaign_id' => $campaign_id,
			'value'       => 5.00,
			'status'      => 'open',
			'currency'    => 'EUR'
		]);

		$transaction_3 = new TransactionEntity([
			'campaign_id' => $campaign_id,
			'value'       => 15.00,
			'status'      => 'paid',
			'currency'    => 'EUR'
		]);

		$transaction_repo->insert($transaction_1);
		$transaction_repo->insert($transaction_2);
		$transaction_repo->insert($transaction_3);

		/** @var CampaignEntity $campaign */
		$campaign = $this->campaign_repository->get($campaign_id);
		$total    = $this->campaign_repository->get_total($campaign);

		$this->assertSame(35.00, $total);
	}
}
