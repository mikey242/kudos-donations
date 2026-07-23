<?php
/**
 * Version430 migration tests.
 */

namespace IseardMedia\Kudos\Tests\Migrations;

use IseardMedia\Kudos\Migrations\Version430;
use IseardMedia\Kudos\Service\SettingsService;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Migrations\Version430
 */
class Version430Test extends BaseTestCase {

	private Version430 $migration;

	public function set_up(): void {
		parent::set_up();
		$this->migration = $this->get_from_container( Version430::class );
		delete_option( SettingsService::SETTING_ONBOARDING_DISMISSED );
	}

	public function tear_down(): void {
		delete_option( SettingsService::SETTING_ONBOARDING_DISMISSED );
		parent::tear_down();
	}

	/**
	 * The migration must resolve from the container, otherwise it never runs.
	 */
	public function test_migration_is_registered(): void {
		$this->assertInstanceOf( Version430::class, $this->migration );
		$this->assertSame( '4.3.0', $this->migration->get_version() );
		$this->assertTrue( $this->migration->is_auto() );
	}

	/**
	 * Any install upgrading to 4.3.0 has already been set up, so onboarding is dismissed
	 * regardless of provider state — the banner is only for fresh installs.
	 */
	public function test_dismisses_onboarding_for_existing_install(): void {
		$this->assertTrue( SettingsService::is_onboarding_active() );

		$this->migration->dismiss_onboarding_for_existing_install();

		$this->assertFalse( SettingsService::is_onboarding_active() );
		$this->assertTrue( (bool) get_option( SettingsService::SETTING_ONBOARDING_DISMISSED ) );
	}
}