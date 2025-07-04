<?php
/**
 * TransactionRepository tests.
 */

namespace Repository;

use BaseTestCase;
use IseardMedia\Kudos\Repository\BaseRepository;
use IseardMedia\Kudos\Repository\CampaignRepository;
use IseardMedia\Kudos\Repository\TransactionRepository;

/**
 * @covers \IseardMedia\Kudos\Repository\TransactionRepository
 */
class TransactionRepositoryTest extends BaseTestCase {

	private TransactionRepository $transaction_repository;
	private int $campaign_id;

	public function set_up(): void {
		parent::set_up();

		$this->transaction_repository = $this->get_repository(TransactionRepository::class);
		$campaign_repository          = $this->get_repository(CampaignRepository::class);

		$this->campaign_id = $campaign_repository->save([
			BaseRepository::TITLE => 'Default Campaign'
		]);
	}

	/**
	 * Test that transaction is created and returned.
	 */
	public function test_save_creates_transaction(): void {
		$id = $this->transaction_repository->save([
			'campaign_id' => $this->campaign_id,
			'value'       => 10.50,
			'status'      => 'paid',
			'currency'    => 'EUR'
		]);

		$this->assertIsInt($id);
		$this->assertGreaterThan(0, $id);
	}

	/**
	 * Test that find() returns transaction by ID.
	 */
	public function test_find_returns_transaction_by_id(): void {
		$id = $this->transaction_repository->save([
			'campaign_id' => $this->campaign_id,
			'value'       => 25.00,
			'status'      => 'paid',
			'currency'    => 'EUR'
		]);

		$transaction = $this->transaction_repository->find($id);

		$this->assertNotNull($transaction);
		$this->assertSame('paid', $transaction['status']);
	}

	/**
	 * Test that save() updates an existing transaction.
	 */
	public function test_save_updates_transaction(): void {
		$id = $this->transaction_repository->save([
			'campaign_id' => $this->campaign_id,
			'value'       => 20.00,
			'status'      => 'open',
			'currency'    => 'EUR'
		]);

		$this->transaction_repository->save([
			BaseRepository::ID => $id,
			'status'           => 'cancelled'
		]);

		$updated = $this->transaction_repository->find($id);
		$this->assertSame('cancelled', $updated['status']);
	}

	/**
	 * Test find_by() returns matching transaction(s).
	 */
	public function test_find_by_returns_matching_transactions(): void {
		$this->transaction_repository->save([
			'campaign_id' => $this->campaign_id,
			'value'       => 15.00,
			'status'      => 'refunded',
			'currency'    => 'EUR'
		]);

		$results = $this->transaction_repository->find_by([
			'status' => 'refunded'
		]);

		$this->assertIsArray($results);
		$this->assertCount(1, $results);
		$this->assertSame('refunded', $results[0]['status']);
	}

	/**
	 * Test that all() returns all transactions.
	 */
	public function test_all_returns_all_transactions(): void {
		$this->transaction_repository->save([
			'campaign_id' => $this->campaign_id,
			'value'       => 5.00,
			'status'      => 'paid',
			'currency'    => 'EUR'
		]);

		$this->transaction_repository->save([
			'campaign_id' => $this->campaign_id,
			'value'       => 9.99,
			'status'      => 'failed',
			'currency'    => 'EUR'
		]);

		$all = $this->transaction_repository->all();

		$this->assertIsArray($all);
		$this->assertCount(2, $all);
	}

	/**
	 * Test that find() returns null for invalid ID.
	 */
	public function test_find_returns_null_for_invalid_id(): void {
		$transaction = $this->transaction_repository->find(99999);
		$this->assertNull($transaction);
	}

	/**
	 * Test delete() removes transaction from database.
	 */
	public function test_delete_removes_transaction(): void {
		$id = $this->transaction_repository->save([
			'campaign_id' => $this->campaign_id,
			'value'       => 30.00,
			'status'      => 'paid',
			'currency'    => 'EUR'
		]);

		$deleted = $this->transaction_repository->delete($id);
		$this->assertTrue($deleted);

		$transaction = $this->transaction_repository->find($id);
		$this->assertNull($transaction);
	}
}
