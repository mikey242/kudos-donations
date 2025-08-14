<?php
/**
 * DonorRepository tests.
 */

namespace IseardMedia\Kudos\Tests\Repository;

use IseardMedia\Kudos\Tests\BaseTestCase;
use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;

/**
 * @covers \IseardMedia\Kudos\Domain\Repository\DonorRepository
 */
class DonorRepositoryTest extends BaseTestCase {

	private DonorRepository $donor_repository;

	public function set_up(): void {
		parent::set_up();
		$this->donor_repository = $this->get_from_container(DonorRepository::class);
	}

	/**
	 * Test that donor is created and returned.
	 */
	public function test_save_creates_donor(): void {
		$donor = new DonorEntity([ 'title' => 'Test Donor']);
		$id = $this->donor_repository->insert($donor);

		$this->assertIsInt($id);
		$this->assertGreaterThan(0, $id);
	}

	/**
	 * Test that donor is found by id.
	 */
	public function test_find_returns_donor_by_id(): void {
		$donor = new DonorEntity([ 'title' => 'Find me' ]);
		$id = $this->donor_repository->insert($donor);

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
		$id = $this->donor_repository->insert($donor);
		$donor->id = $id;
		$donor->email = 'updated@example.com';
		$this->donor_repository->update($donor);

		/** @var DonorEntity $updated */
		$updated = $this->donor_repository->get($id);

		$this->assertSame('updated@example.com', $updated->email);
	}

	/**
	 * Test that find_by() returns expected donor.
	 */
	public function test_find_by_returns_expected_results(): void {
		$donor = new DonorEntity([ 'email' => 'unique@example.com' ]);
		$this->donor_repository->insert($donor);
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
		$this->assertCount(0, $results);
	}

	/**
	 * Test delete() removes donor from database.
	 */
	public function test_delete_removes_donor(): void {
		$donor = new DonorEntity([ 'email' => 'todelete@example.com' ]);
		$id = $this->donor_repository->insert($donor);
		$deleted = $this->donor_repository->delete($id);

		$this->assertTrue($deleted);
		$found = $this->donor_repository->get($id);
		$this->assertNull($found);
	}
}
