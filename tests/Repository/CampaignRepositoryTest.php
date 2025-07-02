<?php
/**
 * CampaignRepository tests.
 */

namespace Repository;

use BaseTestCase;
use IseardMedia\Kudos\Repository\BaseRepository;
use IseardMedia\Kudos\Repository\CampaignRepository;
use IseardMedia\Kudos\Repository\TransactionRepository;

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
		$id = $this->campaign_repository->save([
			BaseRepository::TITLE => 'Test Campaign'
		]);

		$this->assertIsInt($id);
		$this->assertGreaterThan(0, $id);
	}

	/**
	 * Test that campaign is found by id.
	 */
	public function test_find_returns_campaign_by_id(): void {
		$id = $this->campaign_repository->save([
			BaseRepository::TITLE => 'Find Me'
		]);

		$campaign = $this->campaign_repository->find($id);

		$this->assertNotNull($campaign);
		$this->assertSame('Find Me', $campaign[BaseRepository::TITLE]);
	}

	/**
	 * Test that all() returns all campaigns.
	 */
	public function test_all_returns_all_campaigns(): void {
		$this->campaign_repository->save([
			BaseRepository::TITLE => 'One'
		]);

		$this->campaign_repository->save([
			BaseRepository::TITLE => 'Two'
		]);

		$all = $this->campaign_repository->all();

		$this->assertIsArray($all);
		$this->assertCount(2, $all);
	}

	/**
	 * Test that save() updates existing campaign when ID is provided.
	 */
	public function test_save_updates_existing_campaign(): void {
		$id = $this->campaign_repository->save([
			BaseRepository::TITLE => 'Original'
		]);

		$this->campaign_repository->save([
			BaseRepository::ID    => $id,
			BaseRepository::TITLE => 'Updated'
		]);

		$updated = $this->campaign_repository->find($id);

		$this->assertSame('Updated', $updated[ BaseRepository::TITLE]);
	}

	/**
	 * Test that find() returns null for invalid ID.
	 */
	public function test_find_returns_null_for_invalid_id(): void {
		$campaign = $this->campaign_repository->find(99999);
		$this->assertNull($campaign);
	}

	/**
	 * Test that find_by() returns expected campaign.
	 */
	public function test_find_by_returns_matching_campaign(): void {
		$this->campaign_repository->save([
			BaseRepository::TITLE => 'Special Campaign'
		]);

		$results = $this->campaign_repository->find_by([
			BaseRepository::TITLE => 'Special Campaign'
		]);

		$this->assertCount(1, $results);
		$this->assertSame('Special Campaign', $results[0][ BaseRepository::TITLE]);
	}

	/**
	 * Test that find_by() returns empty array when no match.
	 */
	public function test_find_by_returns_empty_array_for_no_match(): void {
		$results = $this->campaign_repository->find_by([
			BaseRepository::TITLE => 'Nonexistent'
		]);

		$this->assertIsArray($results);
		$this->assertCount(0, $results);
	}

	/**
	 * Test delete() removes campaign from database.
	 */
	public function test_delete_removes_campaign(): void {
		$id = $this->campaign_repository->save([
			BaseRepository::TITLE => 'To Delete'
		]);

		$deleted = $this->campaign_repository->delete($id);
		$this->assertTrue($deleted);

		$campaign = $this->campaign_repository->find($id);
		$this->assertNull($campaign);
	}

	/**
	 * Test that get_transactions() returns linked transactions.
	 */
	public function test_get_transactions_returns_linked_transactions(): void {
		$campaign_id = $this->campaign_repository->save([
			BaseRepository::TITLE => 'Linked Campaign'
		]);

		$transaction_repo = new TransactionRepository($this->wpdb);

		// Create 2 transactions linked to the campaign
		$transaction_repo->save([
			'campaign_id' => $campaign_id,
			'value'       => 15.00,
			'status'      => 'paid',
			'currency'    => 'EUR'
		]);

		$transaction_repo->save([
			'campaign_id' => $campaign_id,
			'value'       => 30.00,
			'status'      => 'open',
			'currency'    => 'EUR'
		]);

		$campaign = $this->campaign_repository->find($campaign_id);
		$transactions = $this->campaign_repository->get_transactions($campaign);

		$this->assertIsArray($transactions);
		$this->assertCount(2, $transactions);
	}

	/**
	 * Test that get_total() returns the sum of 'paid' transactions.
	 */
	public function test_get_total_returns_sum_of_paid_transactions(): void {
		$campaign_id = $this->campaign_repository->save([
			BaseRepository::TITLE => 'Total Campaign'
		]);

		$transaction_repo = new TransactionRepository($this->wpdb);

		$transaction_repo->save([
			'campaign_id' => $campaign_id,
			'value'       => 20.00,
			'status'      => 'paid',
			'currency'    => 'EUR'
		]);

		$transaction_repo->save([
			'campaign_id' => $campaign_id,
			'value'       => 5.00,
			'status'      => 'open',
			'currency'    => 'EUR'
		]);

		$transaction_repo->save([
			'campaign_id' => $campaign_id,
			'value'       => 15.00,
			'status'      => 'paid',
			'currency'    => 'EUR'
		]);

		$campaign = $this->campaign_repository->find($campaign_id);
		$total = $this->campaign_repository->get_total($campaign);

		$this->assertSame(35.00, $total);
	}
}
