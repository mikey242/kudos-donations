<?php
/**
 * Bootstrap dev commands.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

use IseardMedia\Kudos\Dev\Fixtures\AllFixtures;
use IseardMedia\Kudos\Dev\Fixtures\CampaignFixtures;
use IseardMedia\Kudos\Dev\Fixtures\DonorsFixtures;
use IseardMedia\Kudos\Dev\Fixtures\TransactionFixtures;

// Add dev commands.

if ( \defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'kudos:fixtures:campaigns',
		new CampaignFixtures(),
	);

	WP_CLI::add_command(
		'kudos:fixtures:transactions',
		new TransactionFixtures(),
	);

	WP_CLI::add_command(
		'kudos:fixtures:donors',
		new DonorsFixtures(),
	);

	WP_CLI::add_command(
		'kudos:fixtures:all',
		new AllFixtures(),
	);
}
