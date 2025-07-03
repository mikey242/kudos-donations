<?php

use IseardMedia\Kudos\Lifecycle\SchemaInstaller;
use IseardMedia\Kudos\Repository\CampaignRepository;
use IseardMedia\Kudos\Repository\DonorRepository;
use IseardMedia\Kudos\Repository\RepositoryAwareInterface;
use IseardMedia\Kudos\Repository\RepositoryAwareTrait;
use IseardMedia\Kudos\Repository\RepositoryManager;
use IseardMedia\Kudos\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Repository\TransactionRepository;

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

		// Clear schema
		$this->reset_plugin_schema();

	}

	/**
	 * Sets up the repositories and the manager.
	 */
	private function configure_repositories() {
		// Initialize all repositories
		$repositories = [
			new CampaignRepository($this->wpdb),
			new TransactionRepository($this->wpdb),
			new DonorRepository($this->wpdb),
			new SubscriptionRepository($this->wpdb),
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
	 * Clear the schema between runs.
	 */
	private function reset_plugin_schema() {
		error_log("Resetting plugin schema between tests");
		foreach ( SchemaInstaller::TABLE_NAMES as $table ) {
			$full_table = $this->wpdb->table($table);
			error_log("Running query: DROP TABLE IF EXISTS $full_table");
			$this->wpdb->query("DROP TABLE IF EXISTS $full_table");
		}
	}
}
