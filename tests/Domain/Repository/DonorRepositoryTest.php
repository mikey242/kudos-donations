<?php
/**
 * DonorRepository tests.
 */

namespace IseardMedia\Kudos\Tests\Domain\Repository;

use InvalidArgumentException;
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

	/**
	 * Test upsert() inserts a new entity when no ID is set.
	 */
	public function test_upsert_inserts_new_entity(): void {
		$donor = new DonorEntity([ 'email' => 'upsert-new@example.com' ]);
		$id = $this->donor_repository->upsert($donor);

		$this->assertIsInt($id);
		$this->assertGreaterThan(0, $id);
	}

	/**
	 * Test upsert() updates an existing entity when ID is set.
	 */
	public function test_upsert_updates_existing_entity(): void {
		$donor = new DonorEntity([ 'email' => 'upsert-existing@example.com' ]);
		$id = $this->donor_repository->insert($donor);

		$donor->id = $id;
		$donor->email = 'upsert-updated@example.com';
		$result = $this->donor_repository->upsert($donor);

		$this->assertSame($id, $result);
		/** @var DonorEntity $updated */
		$updated = $this->donor_repository->get($id);
		$this->assertSame('upsert-updated@example.com', $updated->email);
	}

	/**
	 * Test patch() updates specific fields.
	 */
	public function test_patch_updates_specific_fields(): void {
		$donor = new DonorEntity([ 'email' => 'patch@example.com', 'name' => 'Original' ]);
		$id = $this->donor_repository->insert($donor);

		$this->donor_repository->patch($id, [ 'name' => 'Patched' ]);

		/** @var DonorEntity $updated */
		$updated = $this->donor_repository->get($id);
		$this->assertSame('Patched', $updated->name);
		$this->assertSame('patch@example.com', $updated->email);
	}

	/**
	 * Test patch() returns false for nonexistent ID.
	 */
	public function test_patch_returns_false_for_nonexistent_id(): void {
		$result = $this->donor_repository->patch(999999, [ 'name' => 'Ghost' ]);
		$this->assertFalse($result);
	}

	/**
	 * Test count_query() with WHERE criteria.
	 */
	public function test_count_query_returns_correct_count(): void {
		$this->donor_repository->insert(new DonorEntity([ 'email' => 'count1@example.com', 'city' => 'Amsterdam' ]));
		$this->donor_repository->insert(new DonorEntity([ 'email' => 'count2@example.com', 'city' => 'Amsterdam' ]));
		$this->donor_repository->insert(new DonorEntity([ 'email' => 'count3@example.com', 'city' => 'Berlin' ]));

		$count = $this->donor_repository->count_query([ 'city' => 'Amsterdam' ]);
		$this->assertSame(2, $count);
	}

	/**
	 * Test count_query() with empty WHERE returns total rows.
	 */
	public function test_count_query_empty_where_returns_total(): void {
		$this->donor_repository->insert(new DonorEntity([ 'email' => 'total1@example.com' ]));
		$this->donor_repository->insert(new DonorEntity([ 'email' => 'total2@example.com' ]));

		$count = $this->donor_repository->count_query();
		$this->assertSame(2, $count);
	}

	/**
	 * Test query() with orderby.
	 */
	public function test_query_with_orderby(): void {
		$this->donor_repository->insert(new DonorEntity([ 'title' => 'Beta', 'email' => 'beta@example.com' ]));
		$this->donor_repository->insert(new DonorEntity([ 'title' => 'Alpha', 'email' => 'alpha@example.com' ]));

		/** @var DonorEntity[] $results */
		$results = $this->donor_repository->query([ 'orderby' => 'title', 'order' => 'ASC' ]);

		$this->assertCount(2, $results);
		$this->assertSame('Alpha', $results[0]->title);
		$this->assertSame('Beta', $results[1]->title);
	}

	/**
	 * Test query() with limit and offset.
	 */
	public function test_query_with_limit_and_offset(): void {
		$this->donor_repository->insert(new DonorEntity([ 'title' => 'First', 'email' => 'first@example.com' ]));
		$this->donor_repository->insert(new DonorEntity([ 'title' => 'Second', 'email' => 'second@example.com' ]));
		$this->donor_repository->insert(new DonorEntity([ 'title' => 'Third', 'email' => 'third@example.com' ]));

		/** @var DonorEntity[] $results */
		$results = $this->donor_repository->query([ 'orderby' => 'id', 'order' => 'ASC', 'limit' => 1, 'offset' => 1 ]);

		$this->assertCount(1, $results);
		$this->assertSame('Second', $results[0]->title);
	}

	/**
	 * Test query() silently ignores invalid orderby column.
	 */
	public function test_query_ignores_invalid_orderby(): void {
		$this->donor_repository->insert(new DonorEntity([ 'email' => 'test@example.com' ]));

		$results = $this->donor_repository->query([ 'orderby' => 'nonexistent_column' ]);

		$this->assertCount(1, $results);
	}

	/**
	 * Test query() filters out invalid columns.
	 */
	public function test_query_ignores_invalid_columns(): void {
		$this->donor_repository->insert(new DonorEntity([ 'email' => 'cols@example.com' ]));

		$results = $this->donor_repository->query([ 'columns' => [ 'email', 'fake_column' ] ]);

		$this->assertCount(1, $results);
		$this->assertSame('cols@example.com', $results[0]->email);
	}

	/**
	 * Test new_entity() applies type casting.
	 */
	public function test_new_entity_applies_type_casting(): void {
		/** @var DonorEntity $entity */
		$entity = $this->donor_repository->new_entity([ 'id' => '42', 'email' => 'cast@example.com' ]);

		$this->assertSame(42, $entity->id);
		$this->assertInstanceOf(DonorEntity::class, $entity);
	}

	/**
	 * Test insert() generates title when empty.
	 */
	public function test_insert_generates_title_when_empty(): void {
		$donor = new DonorEntity([ 'email' => 'autotitle@example.com' ]);
		$id = $this->donor_repository->insert($donor);

		/** @var DonorEntity $result */
		$result = $this->donor_repository->get($id);
		$this->assertNotEmpty($result->title);
		$this->assertStringContainsString('Donor', $result->title);
	}

	/**
	 * Test find_one_by() returns a single entity.
	 */
	public function test_find_one_by_returns_single_entity(): void {
		$this->donor_repository->insert(new DonorEntity([ 'email' => 'findone@example.com' ]));

		$result = $this->donor_repository->find_one_by([ 'email' => 'findone@example.com' ]);
		$this->assertInstanceOf(DonorEntity::class, $result);
		$this->assertSame('findone@example.com', $result->email);
	}

	/**
	 * Test find_one_by() returns null when no match.
	 */
	public function test_find_one_by_returns_null_for_no_match(): void {
		$result = $this->donor_repository->find_one_by([ 'email' => 'nobody@example.com' ]);
		$this->assertNull($result);
	}

	/**
	 * Test update() throws InvalidArgumentException without ID.
	 */
	public function test_update_throws_without_id(): void {
		$this->expectException(InvalidArgumentException::class);

		$donor = new DonorEntity([ 'email' => 'noid@example.com' ]);
		$this->donor_repository->update($donor);
	}
}
