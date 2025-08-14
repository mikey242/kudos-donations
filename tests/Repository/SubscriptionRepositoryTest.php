<?php
/**
 * SubscriptionRepository tests.
 */

namespace IseardMedia\Kudos\Tests\Repository;

use IseardMedia\Kudos\Tests\BaseTestCase;
use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;

/**
 * @covers \IseardMedia\Kudos\Domain\Repository\SubscriptionRepository
 */
class SubscriptionRepositoryTest extends BaseTestCase {

	private SubscriptionRepository $subscription_repository;
	private int $campaign_id;

	public function set_up(): void {
		parent::set_up();

		$this->subscription_repository = $this->get_from_container(SubscriptionRepository::class);
		$campaign_repository          = $this->get_from_container(CampaignRepository::class);

		$campaign = new CampaignEntity([ 'title' => 'Default Campaign']);
		$this->campaign_id = $campaign_repository->insert($campaign);
	}

	/**
	 * Test that subscription is created and returned.
	 */
	public function test_save_creates_subscription(): void {
		$subscription = new SubscriptionEntity([
			'campaign_id' => $this->campaign_id,
			'value'       => 10.50,
			'currency'    => 'EUR'
		]);
		$id = $this->subscription_repository->insert($subscription);

		$this->assertIsInt($id);
		$this->assertGreaterThan(0, $id);
	}

	/**
	 * Test that find() returns subscription by ID.
	 */
	public function test_find_returns_subscription_by_id(): void {
		$subscription = new SubscriptionEntity([
			'campaign_id' => $this->campaign_id,
			'value'       => 25.00,
			'status'      => 'active',
			'currency'    => 'EUR'
		]);
		$id = $this->subscription_repository->insert($subscription);

		/** @var ?SubscriptionEntity $subscription */
		$subscription = $this->subscription_repository->get($id);

		$this->assertNotNull($subscription);
		$this->assertSame('active', $subscription->status);
	}

	/**
	 * Test that save() updates an existing subscription.
	 */
	public function test_save_updates_subscription(): void {
		$subscription = new SubscriptionEntity([
			'campaign_id' => $this->campaign_id,
			'value'       => 20.00,
			'status'      => 'active',
			'currency'    => 'EUR'
		]);
		$id = $this->subscription_repository->insert($subscription);

		$subscription->status = 'cancelled';
		$subscription->id = $id;
		$this->subscription_repository->update($subscription);

		/** @var SubscriptionEntity $updated */
		$updated = $this->subscription_repository->get($id);
		$this->assertSame('cancelled', $updated->status);
	}

	/**
	 * Test find_by() returns matching subscription(s).
	 */
	public function test_find_by_returns_matching_subscriptions(): void {
		$subscription = new SubscriptionEntity([
			'campaign_id' => $this->campaign_id,
			'value'       => 15.00,
			'status'      => 'pending',
			'currency'    => 'EUR'
		]);
		$this->subscription_repository->insert($subscription);

		/** @var SubscriptionEntity[] $results */
		$results = $this->subscription_repository->find_by([
			'status' => 'pending'
		]);

		$this->assertNotEmpty($results);
		$this->assertCount(1, $results);
		$this->assertSame('pending', $results[0]->status);
	}

	/**
	 * Test that all() returns all subscriptions.
	 */
	public function test_all_returns_all_subscriptions(): void {

		$subscription_1 = new SubscriptionEntity([
			'campaign_id' => $this->campaign_id,
			'value'       => 5.00,
			'status'      => 'active',
			'currency'    => 'EUR'
		]);
		$subscription_2 = new SubscriptionEntity([
			'campaign_id' => $this->campaign_id,
			'value'       => 9.99,
			'status'      => 'suspended',
			'currency'    => 'EUR'
		]);

		$this->subscription_repository->insert($subscription_2);
		$this->subscription_repository->insert($subscription_1);

		$all = $this->subscription_repository->all();

		$this->assertNotEmpty($all);
		$this->assertCount(2, $all);
	}

	/**
	 * Test that find() returns null for invalid ID.
	 */
	public function test_find_returns_null_for_invalid_id(): void {
		$subscription = $this->subscription_repository->get(99999);
		$this->assertNull($subscription);
	}

	/**
	 * Test delete() removes subscription from database.
	 */
	public function test_delete_removes_subscription(): void {
		$subscription = new SubscriptionEntity([
			'campaign_id' => $this->campaign_id,
			'value'       => 30.00,
			'status'      => 'active',
			'currency'    => 'EUR'
		]);
		$id = $this->subscription_repository->insert($subscription);

		$deleted = $this->subscription_repository->delete($id);
		$this->assertTrue($deleted);

		$subscription = $this->subscription_repository->get($id);
		$this->assertNull($subscription);
	}
}
