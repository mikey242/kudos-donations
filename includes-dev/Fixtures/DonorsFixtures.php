<?php
/**
 * Used to manage and create donor fixtures.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Dev\Fixtures;

use IseardMedia\Kudos\Repository\DonorRepository;

class DonorsFixtures extends BaseFixtures {
	/**
	 * {@inheritDoc}
	 */
	protected function before(): void {
		$this->repository = new DonorRepository( $this->wpdb );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function generate_random_entity(): array {
		return [
			'name'               => $this->faker->name(),
			'email'              => $this->faker->email(),
			'country'            => $this->faker->countryCode(),
			'business_name'      => $this->faker->company(),
			'postcode'           => $this->faker->postcode(),
			'street'             => $this->faker->streetAddress(),
			'city'               => $this->faker->city(),
			'vendor_customer_id' => 'cst_' . wp_rand( 1000000, 9999999 ),
		];
	}
}
