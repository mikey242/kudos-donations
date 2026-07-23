<?php
/**
 * Migration to skip onboarding on installs that upgraded to 4.3.0.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Service\SettingsService;

class Version430 extends BaseMigration {

	protected string $version = '4.3.0';

	/**
	 * {@inheritDoc}
	 */
	public function is_auto(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_jobs(): array {
		return [
			'dismiss_onboarding_for_existing_install' => $this->job( [ $this, 'dismiss_onboarding_for_existing_install' ], 'Skipping onboarding for existing install', false ),
		];
	}

	/**
	 * Dismisses the onboarding banner for any install upgrading to 4.3.0.
	 */
	public function dismiss_onboarding_for_existing_install(): void {
		update_option( SettingsService::SETTING_ONBOARDING_DISMISSED, true );
		$this->logger->info( 'Onboarding marked as dismissed: existing install upgraded to 4.3.0.' );
	}
}
