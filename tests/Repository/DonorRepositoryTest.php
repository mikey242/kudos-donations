<?php
/**
 * DonorRepository tests.
 */

namespace Repository;

use BaseTestCase;
use IseardMedia\Kudos\Entity\CampaignEntity;
use IseardMedia\Kudos\Entity\DonorEntity;
use IseardMedia\Kudos\Repository\DonorRepository;

/**
 * @covers \IseardMedia\Kudos\Repository\DonorRepository
 */
class DonorRepositoryTest extends BaseTestCase {

	private DonorRepository $donor_repository;

	public function set_up(): void {
		parent::set_up();
		$this->donor_repository = $this->get_repository(DonorRepository::class);
	}

	/**
	 * Test that donor is created and returned.
	 */
	public function test_save_creates_campaign(): void {
		$campaign = new CampaignEntity([ 'title' => 'Test Donor']);
		$id = $this->donor_repository->upsert($campaign);

		$this->assertIsInt($id);
		$this->assertGreaterThan(0, $id);
	}

	/**
	 * Test that donor is found by id.
	 */
	public function test_find_returns_donor_by_id(): void {
		$donor = new DonorEntity([ 'title' => 'Find me' ]);
		$id = $this->donor_repository->upsert($donor);

		$donor = $this->donor_repository->get($id);

		$this->assertNotNull($donor);
		$this->assertSame('Find me', $donor->title);
	}

	/**
	 * Test that all() returns all donors.
	 */
	public function test_all_returns_all_donors(): void {
		$donor_1 = new DonorEntity([ 'email' => 'donor1@example.com' ]);
		$donor_2 = new DonorEntity([ 'email' => 'donor2@example.com' ]);
		$this->donor_repository->insert($donor_1);
		$this->donor_repository->insert($donor_2);

		$all = $this->donor_repository->all();

		$this->assertCount(2, $all);
	}

	/**
	 * Test that save() updates existing donor when ID is provided.
	 */
	public function test_save_updates_existing_donor(): void {
		$donor = new DonorEntity([ 'email' => 'original@example.com' ]);
		$id = $this->donor_repository->upsert($donor);
		$donor->id = $id;
		$donor->email = 'updated@example.com';
		$this->donor_repository->upsert($donor);

		/** @var DonorEntity $updated */
		$updated = $this->donor_repository->get($id);

		$this->assertSame('updated@example.com', $updated->email);
	}

	/**
	 * Test that find_by() returns expected donor.
	 */
	public function test_find_by_returns_expected_results(): void {
		$donor = new DonorEntity([ 'email' => 'unique@example.com' ]);
		$this->donor_repository->upsert($donor);
		/** @var DonorEntity[] $results */
		$results = $this->donor_repository->find_by([ 'email' => 'unique@example.com' ]);

		$this->assertCount(1, $results);
		$this->assertSame('unique@example.com', $results[0]->email);
	}

	/**
	 * Test that find() returns null for invalid ID.
	 */
	public function test_find_returns_null_for_invalid_id(): void {
		$result = $this->donor_repository->get(999999); // unlikely to exist
		$this->assertNull($result);
	}

	/**
	 * Test that find_by() returns empty array when no match.
	 */
	public function test_find_by_returns_empty_array_for_no_match(): void {
		$results = $this->donor_repository->find_by([ 'email' => 'nonexistent@example.com' ]);
		$this->assertIsArray($results);
		$this->assertCount(0, $results);
	}

	/**
	 * Test delete() removes donor from database.
	 */
	public function test_delete_removes_donor(): void {
		$donor = new DonorEntity([ 'email' => 'todelete@example.com' ]);
		$id = $this->donor_repository->upsert($donor);
		$deleted = $this->donor_repository->delete($id);

		$this->assertTrue($deleted);
		$found = $this->donor_repository->get($id);
		$this->assertNull($found);
	}
}
