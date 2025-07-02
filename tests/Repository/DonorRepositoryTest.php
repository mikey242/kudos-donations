<?php
/**
 * DonorRepository tests.
 */

namespace Repository;

use BaseTestCase;
use IseardMedia\Kudos\Repository\BaseRepository;
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
		$id = $this->donor_repository->save([
			BaseRepository::TITLE => 'Test Donor'
		]);

		$this->assertIsInt($id);
		$this->assertGreaterThan(0, $id);
	}

	/**
	 * Test that donor is found by id.
	 */
	public function test_find_returns_donor_by_id(): void {
		$id = $this->donor_repository->save([ BaseRepository::TITLE => 'Find me' ]);

		$donor = $this->donor_repository->find($id);

		$this->assertNotNull($donor);
		$this->assertSame('Find me', $donor[BaseRepository::TITLE]);
	}

	/**
	 * Test that all() returns all donors.
	 */
	public function test_all_returns_all_donors(): void {
		$this->donor_repository->save([ DonorRepository::EMAIL => 'donor1@example.com' ]);
		$this->donor_repository->save([ DonorRepository::EMAIL => 'donor2@example.com' ]);

		$all = $this->donor_repository->all();

		$this->assertCount(2, $all);
	}

	/**
	 * Test that save() updates existing donor when ID is provided.
	 */
	public function test_save_updates_existing_donor(): void {
		$id = $this->donor_repository->save([ DonorRepository::EMAIL => 'original@example.com' ]);
		$this->donor_repository->save([
			BaseRepository::ID     => $id,
			DonorRepository::EMAIL => 'updated@example.com'
		]);

		$updated = $this->donor_repository->find($id);

		$this->assertSame('updated@example.com', $updated[DonorRepository::EMAIL]);
	}

	/**
	 * Test that find_by() returns expected donor.
	 */
	public function test_find_by_returns_expected_results(): void {
		$this->donor_repository->save([ DonorRepository::EMAIL => 'unique@example.com' ]);
		$results = $this->donor_repository->find_by([ DonorRepository::EMAIL => 'unique@example.com' ]);

		$this->assertCount(1, $results);
		$this->assertSame('unique@example.com', $results[0][DonorRepository::EMAIL]);
	}

	/**
	 * Test that find() returns null for invalid ID.
	 */
	public function test_find_returns_null_for_invalid_id(): void {
		$result = $this->donor_repository->find(999999); // unlikely to exist
		$this->assertNull($result);
	}

	/**
	 * Test that find_by() returns empty array when no match.
	 */
	public function test_find_by_returns_empty_array_for_no_match(): void {
		$results = $this->donor_repository->find_by([ DonorRepository::EMAIL => 'nonexistent@example.com' ]);
		$this->assertIsArray($results);
		$this->assertCount(0, $results);
	}

	/**
	 * Test delete() removes donor from database.
	 */
	public function test_delete_removes_donor(): void {
		$id = $this->donor_repository->save([ DonorRepository::EMAIL => 'todelete@example.com' ]);
		$deleted = $this->donor_repository->delete($id);

		$this->assertTrue($deleted);
		$found = $this->donor_repository->find($id);
		$this->assertNull($found);
	}
}
