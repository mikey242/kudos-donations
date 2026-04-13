<?php
/**
 * Used to manage and create campaign fixtures.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Dev\Fixtures;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Schema\CampaignSchema;

class CampaignFixtures extends BaseFixtures {

	private array $campaign_names = [];

	/**
	 * {@inheritDoc}
	 */
	protected function before(): void {
		$this->repository = new CampaignRepository( $this->wpdb, new CampaignSchema() );
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
		$currencies     = [ 'EUR', 'USD', 'GBP', 'CHF', 'AUD' ];
		$currency       = $currencies[ array_rand( $currencies ) ];

		$title = $this->get_unique_campaign_name( $titles[ array_rand( $titles ) ] );

		return new CampaignEntity(
			[
				'title'               => $title,
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

	/**
	 * Checks if campaign name already created and if so iterates # until unique.
	 *
	 * @param string $base Base name of the campaign to check.
	 * @param int    $count The current iteration.
	 */
	private function get_unique_campaign_name( string $base, int $count = 0 ): string {
		$name = $count > 0 ? $base . ' #' . $count : $base;
		if ( \in_array( $name, $this->campaign_names, true ) ) {
			return $this->get_unique_campaign_name( $base, $count + 1 );
		}
		$this->campaign_names[] = $name;
		return $name;
	}
}
