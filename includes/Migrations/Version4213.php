<?php
/**
 * Migration to clear stale admin notices left over from the notice system refactor.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Service\NoticeService;

class Version4213 extends BaseMigration {

	protected string $version = '4.2.13';

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
			'clear_admin_notices' => $this->job( [ $this, 'clear_admin_notices' ], 'Clearing stale admin notices', false ),
		];
	}

	/**
	 * Clears the persisted admin notices option.
	 *
	 * The notice system was refactored to store notices as structured arrays. Installs
	 * upgrading from an older version may hold notices in a legacy shape (e.g. plain
	 * strings), which causes a TypeError when rendered. Clearing the option removes any
	 * such stale entries; current notices are regenerated on the next request.
	 */
	public function clear_admin_notices(): void {
		delete_option( NoticeService::SETTING_ADMIN_NOTICES );
		$this->logger->info( 'Cleared stale admin notices option.' );
	}
}
