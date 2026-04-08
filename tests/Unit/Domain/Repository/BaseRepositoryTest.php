<?php
/**
 * BaseRepository tests.
 *
 * Uses DonorRepository as the concrete implementation.
 */

namespace IseardMedia\Kudos\Tests\Domain\Repository;

use InvalidArgumentException;
use IseardMedia\Kudos\Tests\BaseTestCase;
use IseardMedia\Kudos\Domain\Entity\BaseEntity;
use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;

/**
 * @covers \IseardMedia\Kudos\Domain\Repository\BaseRepository
 */
class BaseRepositoryTest extends BaseTestCase {

	private DonorRepository $repository;

	public function set_up(): void {
		parent::set_up();
		$this->repository = $this->get_from_container( DonorRepository::class );
	}

	/**
	 * Test that insert() creates a record and returns its ID.
	 */
	public function test_insert_creates_record(): void {
		$id = $this->repository->insert( new DonorEntity( [ 'title' => 'Test Record' ] ) );

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );
	}

	/**
	 * Test that get() returns the correct record by ID.
	 */
	public function test_get_returns_record_by_id(): void {
		$id = $this->repository->insert( new DonorEntity( [ 'title' => 'Find me' ] ) );

		$result = $this->repository->get( $id );

		$this->assertNotNull( $result );
		$this->assertSame( 'Find me', $result->title );
	}

	/**
	 * Test that get() returns null for an ID that does not exist.
	 */
	public function test_get_returns_null_for_invalid_id(): void {
		$result = $this->repository->get( 999999 );

		$this->assertNull( $result );
	}

	/**
	 * Test that all() returns every record.
	 */
	public function test_all_returns_all_records(): void {
		$this->repository->insert( new DonorEntity( [ 'email' => 'all1@example.com' ] ) );
		$this->repository->insert( new DonorEntity( [ 'email' => 'all2@example.com' ] ) );

		$this->assertCount( 2, $this->repository->all() );
	}

	/**
	 * Test that update() persists changes to an existing record.
	 */
	public function test_update_modifies_existing_record(): void {
		$donor = new DonorEntity( [ 'email' => 'before@example.com' ] );
		$id    = $this->repository->insert( $donor );

		$donor->id    = $id;
		$donor->email = 'after@example.com';
		$this->repository->update( $donor );

		/** @var DonorEntity $result */
		$result = $this->repository->get( $id );
		$this->assertSame( 'after@example.com', $result->email );
	}

	/**
	 * Test that update() throws when the entity has no ID.
	 */
	public function test_update_throws_without_id(): void {
		$this->expectException( InvalidArgumentException::class );

		$this->repository->update( new DonorEntity( [ 'email' => 'noid@example.com' ] ) );
	}

	/**
	 * Test that find_by() returns records matching a single criterion.
	 */
	public function test_find_by_returns_matching_records(): void {
		$this->repository->insert( new DonorEntity( [ 'email' => 'match@example.com' ] ) );

		/** @var DonorEntity[] $results */
		$results = $this->repository->find_by( [ 'email' => 'match@example.com' ] );

		$this->assertCount( 1, $results );
		$this->assertSame( 'match@example.com', $results[0]->email );
	}

	/**
	 * Test that find_by() with multiple criteria applies AND conditions.
	 */
	public function test_find_by_with_multiple_criteria(): void {
		$this->repository->insert( new DonorEntity( [ 'email' => 'multi1@example.com', 'city' => 'Amsterdam' ] ) );
		$this->repository->insert( new DonorEntity( [ 'email' => 'multi2@example.com', 'city' => 'Amsterdam' ] ) );
		$this->repository->insert( new DonorEntity( [ 'email' => 'multi3@example.com', 'city' => 'Berlin' ] ) );

		$results = $this->repository->find_by( [ 'city' => 'Amsterdam', 'email' => 'multi1@example.com' ] );

		$this->assertCount( 1, $results );
	}

	/**
	 * Test that find_by() returns an empty array when no records match.
	 */
	public function test_find_by_returns_empty_for_no_match(): void {
		$results = $this->repository->find_by( [ 'email' => 'ghost@example.com' ] );

		$this->assertCount( 0, $results );
	}

	/**
	 * Test that find_one_by() returns a single matching entity.
	 */
	public function test_find_one_by_returns_single_entity(): void {
		$this->repository->insert( new DonorEntity( [ 'email' => 'one@example.com' ] ) );

		$result = $this->repository->find_one_by( [ 'email' => 'one@example.com' ] );

		$this->assertInstanceOf( BaseEntity::class, $result );
		$this->assertSame( 'one@example.com', $result->email );
	}

	/**
	 * Test that find_one_by() returns null when no record matches.
	 */
	public function test_find_one_by_returns_null_for_no_match(): void {
		$result = $this->repository->find_one_by( [ 'email' => 'nobody@example.com' ] );

		$this->assertNull( $result );
	}

	/**
	 * Test that find_one_by() with a null value generates an IS NULL WHERE clause.
	 */
	public function test_find_one_by_with_null_criteria(): void {
		$this->repository->insert( new DonorEntity( [ 'email' => 'nullfield@example.com' ] ) );

		// city is null by default, so this should find the record.
		$result = $this->repository->find_one_by( [ 'city' => null ] );

		$this->assertNotNull( $result );
	}

	/**
	 * Test that delete() removes the record and returns true.
	 */
	public function test_delete_removes_record(): void {
		$id = $this->repository->insert( new DonorEntity( [ 'email' => 'delete@example.com' ] ) );

		$this->assertTrue( $this->repository->delete( $id ) );
		$this->assertNull( $this->repository->get( $id ) );
	}

	/**
	 * Test that delete() returns true even when the ID does not exist (0 rows affected != false).
	 */
	public function test_delete_returns_true_for_nonexistent_id(): void {
		$result = $this->repository->delete( 999999 );

		$this->assertTrue( $result );
	}

	/**
	 * Test that upsert() inserts a new record when no ID is set.
	 */
	public function test_upsert_inserts_new_entity(): void {
		$id = $this->repository->upsert( new DonorEntity( [ 'email' => 'upsert-new@example.com' ] ) );

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );
	}

	/**
	 * Test that upsert() updates an existing record when an ID is set.
	 */
	public function test_upsert_updates_existing_entity(): void {
		$donor = new DonorEntity( [ 'email' => 'upsert-before@example.com' ] );
		$id    = $this->repository->insert( $donor );

		$donor->id    = $id;
		$donor->email = 'upsert-after@example.com';
		$result       = $this->repository->upsert( $donor );

		$this->assertSame( $id, $result );
		/** @var DonorEntity $updated */
		$updated = $this->repository->get( $id );
		$this->assertSame( 'upsert-after@example.com', $updated->email );
	}

	/**
	 * Test that patch() updates only the specified fields.
	 */
	public function test_patch_updates_specific_fields(): void {
		$id = $this->repository->insert( new DonorEntity( [ 'email' => 'patch@example.com', 'name' => 'Before' ] ) );

		$this->repository->patch( $id, [ 'name' => 'After' ] );

		/** @var DonorEntity $result */
		$result = $this->repository->get( $id );
		$this->assertSame( 'After', $result->name );
		$this->assertSame( 'patch@example.com', $result->email );
	}

	/**
	 * Test that patch() returns false when the ID does not exist.
	 */
	public function test_patch_returns_false_for_nonexistent_id(): void {
		$result = $this->repository->patch( 999999, [ 'name' => 'Ghost' ] );

		$this->assertFalse( $result );
	}

	/**
	 * Test that patch() silently ignores columns not present in the schema.
	 */
	public function test_patch_ignores_unknown_columns(): void {
		$id = $this->repository->insert( new DonorEntity( [ 'email' => 'patch-unknown@example.com', 'name' => 'Before' ] ) );

		$this->repository->patch( $id, [ 'name' => 'After', 'nonexistent_column' => 'value' ] );

		/** @var DonorEntity $result */
		$result = $this->repository->get( $id );
		$this->assertSame( 'After', $result->name );
	}

	/**
	 * Test that count_query() returns the correct count for a WHERE criterion.
	 */
	public function test_count_query_returns_correct_count(): void {
		$this->repository->insert( new DonorEntity( [ 'email' => 'count1@example.com', 'city' => 'Amsterdam' ] ) );
		$this->repository->insert( new DonorEntity( [ 'email' => 'count2@example.com', 'city' => 'Amsterdam' ] ) );
		$this->repository->insert( new DonorEntity( [ 'email' => 'count3@example.com', 'city' => 'Berlin' ] ) );

		$this->assertSame( 2, $this->repository->count_query( [ 'city' => 'Amsterdam' ] ) );
	}

	/**
	 * Test that count_query() with no WHERE returns the total row count.
	 */
	public function test_count_query_empty_where_returns_total(): void {
		$this->repository->insert( new DonorEntity( [ 'email' => 'total1@example.com' ] ) );
		$this->repository->insert( new DonorEntity( [ 'email' => 'total2@example.com' ] ) );

		$this->assertSame( 2, $this->repository->count_query() );
	}

	/**
	 * Test that count_query() with a null value generates an IS NULL WHERE clause.
	 */
	public function test_count_query_with_null_where(): void {
		$this->repository->insert( new DonorEntity( [ 'email' => 'null-city@example.com' ] ) ); // city is null by default.

		$count = $this->repository->count_query( [ 'city' => null ] );

		$this->assertGreaterThan( 0, $count );
	}

	/**
	 * Test that query() returns results in ascending order.
	 */
	public function test_query_with_orderby_asc(): void {
		$this->repository->insert( new DonorEntity( [ 'title' => 'Beta', 'email' => 'beta@example.com' ] ) );
		$this->repository->insert( new DonorEntity( [ 'title' => 'Alpha', 'email' => 'alpha@example.com' ] ) );

		/** @var DonorEntity[] $results */
		$results = $this->repository->query( [ 'orderby' => 'title', 'order' => 'ASC' ] );

		$this->assertCount( 2, $results );
		$this->assertSame( 'Alpha', $results[0]->title );
		$this->assertSame( 'Beta', $results[1]->title );
	}

	/**
	 * Test that query() returns results in descending order.
	 */
	public function test_query_with_orderby_desc(): void {
		$this->repository->insert( new DonorEntity( [ 'title' => 'Alpha', 'email' => 'alpha2@example.com' ] ) );
		$this->repository->insert( new DonorEntity( [ 'title' => 'Beta', 'email' => 'beta2@example.com' ] ) );

		/** @var DonorEntity[] $results */
		$results = $this->repository->query( [ 'orderby' => 'title', 'order' => 'DESC' ] );

		$this->assertCount( 2, $results );
		$this->assertSame( 'Beta', $results[0]->title );
		$this->assertSame( 'Alpha', $results[1]->title );
	}

	/**
	 * Test that query() respects limit and offset.
	 */
	public function test_query_with_limit_and_offset(): void {
		$this->repository->insert( new DonorEntity( [ 'title' => 'First', 'email' => 'first@example.com' ] ) );
		$this->repository->insert( new DonorEntity( [ 'title' => 'Second', 'email' => 'second@example.com' ] ) );
		$this->repository->insert( new DonorEntity( [ 'title' => 'Third', 'email' => 'third@example.com' ] ) );

		/** @var DonorEntity[] $results */
		$results = $this->repository->query( [ 'orderby' => 'id', 'order' => 'ASC', 'limit' => 1, 'offset' => 1 ] );

		$this->assertCount( 1, $results );
		$this->assertSame( 'Second', $results[0]->title );
	}

	/**
	 * Test that query() silently ignores an invalid orderby column.
	 */
	public function test_query_ignores_invalid_orderby(): void {
		$this->repository->insert( new DonorEntity( [ 'email' => 'orderby@example.com' ] ) );

		$results = $this->repository->query( [ 'orderby' => 'nonexistent_column' ] );

		$this->assertCount( 1, $results );
	}

	/**
	 * Test that query() filters out invalid column names from the SELECT list.
	 */
	public function test_query_ignores_invalid_columns(): void {
		$this->repository->insert( new DonorEntity( [ 'email' => 'cols@example.com' ] ) );

		$results = $this->repository->query( [ 'columns' => [ 'email', 'fake_column' ] ] );

		$this->assertCount( 1, $results );
		$this->assertSame( 'cols@example.com', $results[0]->email );
	}

	/**
	 * Test that query() with a columns list does not populate unrequested fields.
	 */
	public function test_query_with_specific_columns(): void {
		$this->repository->insert( new DonorEntity( [ 'email' => 'partial@example.com', 'name' => 'Test' ] ) );

		$results = $this->repository->query( [ 'columns' => [ 'email' ] ] );

		$this->assertCount( 1, $results );
		$this->assertSame( 'partial@example.com', $results[0]->email );
		$this->assertFalse( isset( $results[0]->name ) );
	}

	/**
	 * Test that new_entity() applies schema type casting to the provided data.
	 */
	public function test_new_entity_applies_type_casting(): void {
		$entity = $this->repository->new_entity( [ 'id' => '42', 'email' => 'cast@example.com' ] );

		$this->assertSame( 42, $entity->id );
	}

	/**
	 * Test that insert() auto-generates a title when none is provided.
	 */
	public function test_insert_generates_title_when_empty(): void {
		$id = $this->repository->insert( new DonorEntity( [ 'email' => 'autotitle@example.com' ] ) );

		$result = $this->repository->get( $id );
		$this->assertNotEmpty( $result->title );
	}
}