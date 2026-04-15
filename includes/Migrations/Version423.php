<?php
/**
 * Migration to add transaction_id column to kudos_subscriptions table.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Domain\Table\SubscriptionsTable;

class Version423 extends BaseMigration {

	protected string $version = '4.2.3';

	private SubscriptionsTable $subscriptions_table;

	/**
	 * @param SubscriptionsTable $subscriptions_table The subscriptions table class.
	 */
	public function __construct( SubscriptionsTable $subscriptions_table ) {
		$this->subscriptions_table = $subscriptions_table;
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
			'add_transaction_id_column' => $this->job( [ $this, 'add_transaction_id_column' ], 'Adding transaction_id column to subscriptions', false ),
		];
	}

	/**
	 * Runs dbDelta on the subscriptions table to add the missing transaction_id column.
	 */
	public function add_transaction_id_column(): void {
		$this->subscriptions_table->create_table();
		$this->logger->info( 'Added transaction_id column to kudos_subscriptions table.' );
	}
}
