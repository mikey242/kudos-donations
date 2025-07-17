<?php
/**
 * Used to manage and create campaign fixtures.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Dev\Fixtures;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;

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
	protected function generate_random_entity(): CampaignEntity {
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

		return new CampaignEntity(
			[
				'title'               => $title . ' #' . wp_rand( 1000, 9999 ),
				'goal'                => $this->faker->numberBetween( 500, 5000 ),
				'currency'            => $currency,
				'theme_color'         => $this->faker->hexColor(),
				'show_goal'           => $this->faker->boolean(),
				'show_return_message' => $this->faker->boolean(),
				'amount_type'         => $amount_types[ array_rand( $amount_types ) ],
				'donation_type'       => $donation_types[ array_rand( $donation_types ) ],
				'minimum_donation'    => 1,
			]
		);
	}
}
