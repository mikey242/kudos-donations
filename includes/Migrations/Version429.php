<?php
/**
 * Migration to add duration_options column to kudos_campaigns table.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Domain\Table\CampaignsTable;

class Version429 extends BaseMigration {

	protected string $version = '4.2.9';

	private CampaignsTable $campaigns_table;

	/**
	 * @param CampaignsTable $campaigns_table The campaigns table class.
	 */
	public function __construct( CampaignsTable $campaigns_table ) {
		$this->campaigns_table = $campaigns_table;
	}

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
			'add_missing_columns' => $this->job( [ $this, 'add_missing_columns' ], 'Adding missing columns to campaigns table', false ),
		];
	}

	/**
	 * Runs dbDelta on the campaigns table to add any missing columns.
	 */
	public function add_missing_columns(): void {
		$this->campaigns_table->create_table();
		$this->logger->info( 'Added missing columns to kudos_campaigns table.' );
	}
}
