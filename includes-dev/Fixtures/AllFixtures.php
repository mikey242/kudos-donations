<?php
/**
 * Used to manage and create all fixtures.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Dev\Fixtures;

use WP_CLI;
use WP_CLI\ExitException;

class AllFixtures {
	/**
	 * Generate all fixture types (campaigns, donors, transactions, etc.)
	 *
	 * ## OPTIONS
	 *
	 * [--count=<number>]
	 * : Default count for all fixtures.
	 *
	 * [--count-campaign=<number>]
	 * : How many campaigns to create.
	 *
	 * [--count-donor=<number>]
	 * : How many donors to create.
	 *
	 * [--count-transaction=<number>]
	 * : How many transactions to create.
	 *
	 *  [--count-subscription=<number>]
	 *  : How many subscriptions to create.
	 *
	 * [--delete]
	 * : Delete all existing entities before generating new ones.
	 *
	 * [--force]
	 * : Skip confirmation for --delete.
	 *
	 * ## EXAMPLES
	 *
	 * wp kudos:fixtures:all --count-campaign=10 --count-donor=25 --count-transaction=50 --count-subscription=5
	 *
	 * @throws ExitException Thrown on failure to create record.
	 *
	 * @param mixed $args Arguments.
	 * @param mixed $assoc_args Associative arguments.
	 */
	public function __invoke( $args, $assoc_args ): void {

		WP_CLI::log( WP_CLI::colorize( "%3%kRunning all fixture generators...%N\n" ) );

		$fixtures = [
			'campaign'     => new CampaignFixtures(),
			'donor'        => new DonorsFixtures(),
			'subscription' => new SubscriptionFixtures(),
			'transaction'  => new TransactionFixtures(),
		];

		foreach ( $fixtures as $key => $fixture ) {
			$count = $assoc_args[ "count-$key" ] ?? $assoc_args['count'] ?? 5;

			WP_CLI::log( WP_CLI::colorize( "%6%Creating $count $key(s)...%N" ) );

			$fixture( [], array_merge( $assoc_args, [ 'count' => $count ] ) );
		}

		WP_CLI::success( 'All fixtures generated successfully.' );
	}
}
