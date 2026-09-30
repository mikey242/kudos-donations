<?php
/**
 * SettingsService tests.
 */

namespace IseardMedia\Kudos\Tests\Service;

use IseardMedia\Kudos\Container\Handler\MigrationHandler;
use IseardMedia\Kudos\Service\SettingsService;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Service\SettingsService
 */
class SettingsServiceTest extends BaseTestCase {

	public function set_up(): void {
		parent::set_up();
		delete_option( SettingsService::SETTING_ONBOARDING_DISMISSED );
		delete_option( MigrationHandler::SETTING_DB_VERSION );
	}

	public function tear_down(): void {
		delete_option( SettingsService::SETTING_ONBOARDING_DISMISSED );
		delete_option( MigrationHandler::SETTING_DB_VERSION );
		parent::tear_down();
	}

	/**
	 * An install upgraded from before 4.3.0 skips onboarding straight away, before the
	 * Version430 migration has had a chance to persist the dismissal.
	 */
	public function test_onboarding_inactive_for_pre_430_install(): void {
		update_option( MigrationHandler::SETTING_DB_VERSION, '4.2.13' );

		$this->assertFalse( SettingsService::is_onboarding_active() );
		$this->assertTrue( $this->get_onboarding_default() );
	}

	/**
	 * A fresh install has no stored db version, so it resolves to the current one and onboarding shows.
	 */
	public function test_onboarding_active_for_new_install(): void {
		$this->assertTrue( SettingsService::is_onboarding_active() );
		$this->assertFalse( $this->get_onboarding_default() );
	}

	/**
	 * An install already on 4.3.0 that has not dismissed onboarding still sees it.
	 */
	public function test_onboarding_active_for_430_install(): void {
		update_option( MigrationHandler::SETTING_DB_VERSION, '4.3.0' );

		$this->assertTrue( SettingsService::is_onboarding_active() );
		$this->assertFalse( $this->get_onboarding_default() );
	}

	/**
	 * A stored value always wins over the install-based default.
	 */
	public function test_stored_value_overrides_default(): void {
		update_option( MigrationHandler::SETTING_DB_VERSION, '4.2.13' );
		// update_option() would skip writing false when it matches the registered default.
		add_option( SettingsService::SETTING_ONBOARDING_DISMISSED, '0' );

		$this->assertTrue( SettingsService::is_onboarding_active() );
	}

	/**
	 * Returns the registered default for the onboarding setting, as served to the admin UI.
	 */
	private function get_onboarding_default(): bool {
		return SettingsService::get_settings()[ SettingsService::SETTING_ONBOARDING_DISMISSED ]['default'];
	}
}
