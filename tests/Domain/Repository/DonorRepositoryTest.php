<?php
/**
 * DonorRepository tests.
 */

namespace IseardMedia\Kudos\Tests\Domain\Repository;

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
		$this->donor_repository = $this->get_from_container( DonorRepository::class );
	}

	/**
	 * Test that a full country name is converted to an ISO code on insert.
	 * Regression: inserting a full name previously caused a DB error because the column is 2 chars.
	 */
	public function test_insert_with_full_country_name_stores_iso_code(): void {
		$id = $this->donor_repository->insert( new DonorEntity( [ 'email' => 'country-full@example.com', 'country' => 'Netherlands' ] ) );

		$this->assertIsInt( $id );

		/** @var DonorEntity $result */
		$result = $this->donor_repository->get( $id );
		$this->assertSame( 'NL', $result->country );
	}

	/**
	 * Test that a valid ISO country code is stored as-is.
	 */
	public function test_insert_with_iso_country_code_stores_correctly(): void {
		$id = $this->donor_repository->insert( new DonorEntity( [ 'email' => 'country-iso@example.com', 'country' => 'NL' ] ) );

		/** @var DonorEntity $result */
		$result = $this->donor_repository->get( $id );
		$this->assertSame( 'NL', $result->country );
	}

	/**
	 * Test that a lowercase country code is normalised to uppercase.
	 */
	public function test_insert_with_lowercase_country_code_normalises_to_uppercase(): void {
		$id = $this->donor_repository->insert( new DonorEntity( [ 'email' => 'country-lower@example.com', 'country' => 'nl' ] ) );

		/** @var DonorEntity $result */
		$result = $this->donor_repository->get( $id );
		$this->assertSame( 'NL', $result->country );
	}

	/**
	 * Test that an unrecognised country value does not cause an insert failure.
	 * The value should be coerced to empty rather than truncating a long string into the 2-char column.
	 */
	public function test_insert_with_unknown_country_succeeds(): void {
		$id = $this->donor_repository->insert( new DonorEntity( [ 'email' => 'country-unknown@example.com', 'country' => 'Narnia' ] ) );

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );

		/** @var DonorEntity $result */
		$result = $this->donor_repository->get( $id );
		$this->assertEmpty( $result->country );
	}

	/**
	 * Test that the auto-generated title contains the donor singular name.
	 */
	public function test_insert_generates_title_with_donor_label(): void {
		$id = $this->donor_repository->insert( new DonorEntity( [ 'email' => 'autotitle@example.com' ] ) );

		/** @var DonorEntity $result */
		$result = $this->donor_repository->get( $id );
		$this->assertStringContainsString( 'Donor', $result->title );
	}
}