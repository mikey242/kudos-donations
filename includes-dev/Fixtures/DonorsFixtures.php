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
			DonorRepository::NAME               => $this->faker->name(),
			DonorRepository::EMAIL              => $this->faker->email(),
			DonorRepository::COUNTRY            => $this->faker->countryCode(),
			DonorRepository::BUSINESS_NAME      => $this->faker->company(),
			DonorRepository::POSTCODE           => $this->faker->postcode(),
			DonorRepository::STREET             => $this->faker->streetAddress(),
			DonorRepository::CITY               => $this->faker->city(),
			DonorRepository::VENDOR_CUSTOMER_ID => 'cst_' . wp_rand( 1000000, 9999999 ),
		];
	}
}
