<?php
/**
 * Used to manage and create campaign fixtures.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Dev\Fixtures;

use IseardMedia\Kudos\Repository\BaseRepository;
use IseardMedia\Kudos\Repository\CampaignRepository;

class CampaignFixtures extends BaseFixtures {
	/**
	 * {@inheritDoc}
	 */
	protected function before(): void {
		$this->repository = new CampaignRepository( $this->wpdb );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function generate_random_entity(): array {
		$titles = [
			'Save the Forests',
			'Clean Water for All',
			'Books for Schools',
			'Food Relief Fund',
			'Climate Action Now',
			'Healthcare for Everyone',
			'Animal Rescue Campaign',
			'Empower Women Initiative',
		];

		$amount_types   = [ 'open', 'fixed', 'both' ];
		$donation_types = [ 'oneoff', 'recurring', 'both' ];
		$title          = $titles[ array_rand( $titles ) ];
		$currencies     = [ 'EUR', 'USD', 'GBP', 'CHF', 'AUD' ];
		$currency       = $currencies[ array_rand( $currencies ) ];

		return [
			BaseRepository::TITLE                   => $title . ' #' . wp_rand( 1000, 9999 ),
			CampaignRepository::GOAL                => $this->faker->numberBetween( 500, 5000 ),
			CampaignRepository::CURRENCY            => $currency,
			CampaignRepository::THEME_COLOR         => $this->faker->hexColor(),
			CampaignRepository::SHOW_GOAL           => wp_rand( 0, 1 ),
			CampaignRepository::SHOW_RETURN_MESSAGE => wp_rand( 0, 1 ),
			CampaignRepository::AMOUNT_TYPE         => $amount_types[ array_rand( $amount_types ) ],
			CampaignRepository::DONATION_TYPE       => $donation_types[ array_rand( $donation_types ) ],
			CampaignRepository::MINIMUM_DONATION    => 1,
		];
	}
}
