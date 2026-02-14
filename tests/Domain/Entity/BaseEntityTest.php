<?php
/**
 * BaseEntity tests.
 */

namespace IseardMedia\Kudos\Tests\Domain\Entity;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Domain\Entity\BaseEntity
 */
class BaseEntityTest extends BaseTestCase {

	public function test_constructor_assigns_properties(): void {
		$donor = new DonorEntity( [ 'email' => 'test@example.com', 'name' => 'John' ] );
		$this->assertSame( 'test@example.com', $donor->email );
		$this->assertSame( 'John', $donor->name );
	}

	public function test_constructor_applies_defaults(): void {
		$campaign = new CampaignEntity( [] );
		$this->assertSame( 'EUR', $campaign->currency );
		$this->assertSame( 1.0, $campaign->minimum_donation );
		$this->assertFalse( $campaign->show_goal );
	}

	public function test_constructor_data_overrides_defaults(): void {
		$campaign = new CampaignEntity( [ 'currency' => 'USD', 'show_goal' => true ] );
		$this->assertSame( 'USD', $campaign->currency );
		$this->assertTrue( $campaign->show_goal );
	}

	public function test_constructor_skip_defaults(): void {
		$campaign = new CampaignEntity( [ 'title' => 'Test' ], false );
		$this->assertSame( 'Test', $campaign->title );
		// Properties without defaults should remain uninitialized.
		$this->assertFalse( isset( $campaign->currency ) );
	}

	public function test_to_array_excludes_uninitialized_properties(): void {
		$donor = new DonorEntity( [ 'email' => 'test@example.com' ] );
		$array = $donor->to_array();

		// Uninitialized nullable properties should not be present.
		$this->assertArrayNotHasKey( 'mode', $array );
		$this->assertArrayNotHasKey( 'city', $array );
	}

	public function test_to_array_includes_initialized_properties(): void {
		$donor = new DonorEntity( [ 'email' => 'test@example.com', 'name' => 'John' ] );
		$array = $donor->to_array();

		$this->assertArrayHasKey( 'email', $array );
		$this->assertSame( 'test@example.com', $array['email'] );
		$this->assertArrayHasKey( 'name', $array );
		$this->assertSame( 'John', $array['name'] );
	}

	public function test_merge_updates_existing_properties(): void {
		$donor = new DonorEntity( [ 'email' => 'old@example.com' ] );
		$donor->merge( [ 'email' => 'new@example.com', 'name' => 'Jane' ] );

		$this->assertSame( 'new@example.com', $donor->email );
		$this->assertSame( 'Jane', $donor->name );
	}

	public function test_merge_ignores_unknown_keys(): void {
		$donor = new DonorEntity( [ 'email' => 'test@example.com' ] );
		$donor->merge( [ 'nonexistent_property' => 'value' ] );

		$this->assertSame( 'test@example.com', $donor->email );
		$this->assertFalse( property_exists( $donor, 'nonexistent_property' ) );
	}

	public function test_id_defaults_to_null(): void {
		$donor = new DonorEntity( [ 'email' => 'test@example.com' ] );
		$this->assertNull( $donor->id );
	}
}
