<?php
/**
 * Used to manage and create subscription fixtures.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Dev\Fixtures;

use IseardMedia\Kudos\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Repository\SubscriptionRepository;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\SubscriptionStatus;

class SubscriptionFixtures extends BaseFixtures {
	/**
	 * {@inheritDoc}
	 */
	protected function before(): void {
		$this->repository = new SubscriptionRepository( $this->wpdb );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function generate_random_entity(): SubscriptionEntity {
		$vendor_id = 'sub_' . wp_rand( 1000000, 9999999 );

		// 4. Return the subscription data
		return new SubscriptionEntity(
			[
				'frequency'              => $this->pick_weighted(
					[
						'monthly'   => 60,
						'quarterly' => 25,
						'yearly'    => 15,
					]
				),
				'years'                  => wp_rand( 2, 10 ),
				'vendor_subscription_id' => $vendor_id,
				'status'                 => SubscriptionStatus::ACTIVE,
				'value'                  => $this->faker->numberBetween( 20, 200 ),
			]
		);
	}
}
