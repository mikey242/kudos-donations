<?php
/**
 * Version430 migration tests.
 */

namespace IseardMedia\Kudos\Tests\Migrations;

use IseardMedia\Kudos\Migrations\Version430;
use IseardMedia\Kudos\Provider\PaymentProvider\MolliePaymentProvider;
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
		delete_option( MolliePaymentProvider::SETTING_API_KEY_ENCRYPTED_LIVE );
	}

	public function tear_down(): void {
		delete_option( SettingsService::SETTING_ONBOARDING_DISMISSED );
		delete_option( MolliePaymentProvider::SETTING_API_KEY_ENCRYPTED_LIVE );
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
	 * A site with a live API key predates onboarding, so the banner must not appear
	 * and payment status notices must keep working.
	 */
	public function test_configured_site_skips_onboarding(): void {
		update_option( MolliePaymentProvider::SETTING_API_KEY_ENCRYPTED_LIVE, 'encrypted-live-key' );

		$this->migration->skip_onboarding_if_configured();

		$this->assertTrue( SettingsService::is_onboarding_active() === false );
		$this->assertTrue( (bool) get_option( SettingsService::SETTING_ONBOARDING_DISMISSED ) );
	}

	/**
	 * A site without a live API key has not finished setup, so onboarding stays active.
	 */
	public function test_unconfigured_site_keeps_onboarding_active(): void {
		$this->migration->skip_onboarding_if_configured();

		$this->assertTrue( SettingsService::is_onboarding_active() );
		$this->assertFalse( (bool) get_option( SettingsService::SETTING_ONBOARDING_DISMISSED, false ) );
	}
}