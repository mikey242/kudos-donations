<?php

use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\RepositoryAwareInterface;
use IseardMedia\Kudos\Domain\Repository\RepositoryAwareTrait;
use IseardMedia\Kudos\Domain\Repository\RepositoryManager;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Domain\Schema\CampaignSchema;
use IseardMedia\Kudos\Domain\Schema\DonorSchema;
use IseardMedia\Kudos\Domain\Schema\SubscriptionSchema;
use IseardMedia\Kudos\Domain\Schema\TransactionSchema;

abstract class BaseTestCase extends WP_UnitTestCase implements RepositoryAwareInterface {

	use RepositoryAwareTrait;

	protected \IseardMedia\Kudos\Helper\WpDb $wpdb;

	/**
	 * Set up each test and truncate custom plugin tables.
	 */
	public function set_up(): void {
		parent::set_up();

		$this->wpdb = new \IseardMedia\Kudos\Helper\WpDb();

		// Configure repositories.
		$this->configure_repositories();
	}

	/**
	 * Sets up the repositories and the manager.
	 */
	private function configure_repositories() {
		// Initialize all repositories
		$repositories = [
			new CampaignRepository($this->wpdb, new CampaignSchema()),
			new TransactionRepository($this->wpdb, new TransactionSchema()),
			new DonorRepository($this->wpdb, new DonorSchema()),
			new SubscriptionRepository($this->wpdb, new SubscriptionSchema()),
		];

		// Wire up repository manager
		$manager = new RepositoryManager($repositories);

		// Inject repository manager into each repository (so they can call get_repository)
		foreach ( $repositories as $repository ) {
			if ( $repository instanceof RepositoryAwareInterface ) {
				$repository->set_repository_manager($manager);
			}
		}

		// Set the repository manager on tests so they can call it.
		$this->set_repository_manager($manager);
	}

	/**
	 * Helper to assert provided string is a valid URL.
	 */
	protected function assertValidUrl(string $url): void {
		$this->assertNotFalse(
			filter_var($url, FILTER_VALIDATE_URL),
			"Invalid URL: $url"
		);
	}
}
