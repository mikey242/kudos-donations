<?php
/**
 * TransactionRepository tests.
 */

namespace Repository;

use BaseTestCase;
use IseardMedia\Kudos\Entity\CampaignEntity;
use IseardMedia\Kudos\Entity\TransactionEntity;
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

		$campaign = new CampaignEntity([ 'title' => 'Default Campaign']);
		$this->campaign_id = $campaign_repository->upsert($campaign);
	}

	/**
	 * Test that transaction is created and returned.
	 */
	public function test_save_creates_transaction(): void {
		$transaction = new TransactionEntity([
			'campaign_id' => $this->campaign_id,
			'value'       => 10.50,
			'status'      => 'paid',
			'currency'    => 'EUR'
		]);
		$id = $this->transaction_repository->upsert($transaction);

		$this->assertIsInt($id);
		$this->assertGreaterThan(0, $id);
	}

	/**
	 * Test that find() returns transaction by ID.
	 */
	public function test_find_returns_transaction_by_id(): void {
		$transaction = new TransactionEntity([
			'campaign_id' => $this->campaign_id,
			'value'       => 25.00,
			'status'      => 'paid',
			'currency'    => 'EUR'
		]);
		$id = $this->transaction_repository->upsert($transaction);

		/** @var TransactionEntity $transaction */
		$transaction = $this->transaction_repository->get($id);

		$this->assertNotNull($transaction);
		$this->assertSame('paid', $transaction->status);
	}

	/**
	 * Test that save() updates an existing transaction.
	 */
	public function test_save_updates_transaction(): void {
		$transaction = new TransactionEntity([
			'campaign_id' => $this->campaign_id,
			'value'       => 20.00,
			'status'      => 'open',
			'currency'    => 'EUR'
		]);
		$id = $this->transaction_repository->upsert($transaction);

		$transaction->status = 'cancelled';
		$transaction->id = $id;
		$this->transaction_repository->update($transaction);

		/** @var TransactionEntity $updated */
		$updated = $this->transaction_repository->get($id);
		$this->assertSame('cancelled', $updated->status);
	}

	/**
	 * Test find_by() returns matching transaction(s).
	 */
	public function test_find_by_returns_matching_transactions(): void {
		$transaction = new TransactionEntity([
			'campaign_id' => $this->campaign_id,
			'value'       => 15.00,
			'status'      => 'refunded',
			'currency'    => 'EUR'
		]);
		$this->transaction_repository->upsert($transaction);

		/** @var TransactionEntity[] $results */
		$results = $this->transaction_repository->find_by([
			'status' => 'refunded'
		]);

		$this->assertIsArray($results);
		$this->assertCount(1, $results);
		$this->assertSame('refunded', $results[0]->status);
	}

	/**
	 * Test that all() returns all transactions.
	 */
	public function test_all_returns_all_transactions(): void {

		$transaction_1 = new TransactionEntity([
			'campaign_id' => $this->campaign_id,
			'value'       => 5.00,
			'status'      => 'paid',
			'currency'    => 'EUR'
		]);
		$transaction_2 = new TransactionEntity([
			'campaign_id' => $this->campaign_id,
			'value'       => 9.99,
			'status'      => 'failed',
			'currency'    => 'EUR'
		]);

		$this->transaction_repository->upsert($transaction_2);
		$this->transaction_repository->upsert($transaction_1);

		$all = $this->transaction_repository->all();

		$this->assertIsArray($all);
		$this->assertCount(2, $all);
	}

	/**
	 * Test that find() returns null for invalid ID.
	 */
	public function test_find_returns_null_for_invalid_id(): void {
		$transaction = $this->transaction_repository->get(99999);
		$this->assertNull($transaction);
	}

	/**
	 * Test delete() removes transaction from database.
	 */
	public function test_delete_removes_transaction(): void {
		$transaction = new TransactionEntity([
			'campaign_id' => $this->campaign_id,
			'value'       => 30.00,
			'status'      => 'paid',
			'currency'    => 'EUR'
		]);
		$id = $this->transaction_repository->upsert($transaction);

		$deleted = $this->transaction_repository->delete($id);
		$this->assertTrue($deleted);

		$transaction = $this->transaction_repository->get($id);
		$this->assertNull($transaction);
	}
}
