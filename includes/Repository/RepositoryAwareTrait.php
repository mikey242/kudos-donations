<?php
/**
 * Methods for setting up repository manager.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Repository;

use IseardMedia\Kudos\Entity\BaseEntity;

trait RepositoryAwareTrait {

	protected ?RepositoryManager $repository_manager = null;

	/**
	 * Adds the repository manager,
	 *
	 * @param RepositoryManager $repository_manager Instance of repository manager.
	 */
	public function set_repository_manager( RepositoryManager $repository_manager ): void {
		$this->repository_manager = $repository_manager;
	}

	/**
	 * Checks if manager set before returning the repository manager.
	 *
	 * @throws \RuntimeException If manager not set.
	 */
	protected function get_repository_manager(): RepositoryManager {
		if ( ! $this->repository_manager ) {
			throw new \RuntimeException( 'RepositoryManager not set on ' . static::class );
		}
		return $this->repository_manager;
	}

	/**
	 * Get a specific repository instance by FQCN.
	 *
	 * @param class-string<TRepository> $class_name Repository class name.
	 * @return TRepository
	 *
	 * @template TEntity of BaseEntity
	 * @template TRepository of BaseRepository<TEntity>
	 *
	 * @phpcs:disable Squiz.Commenting.FunctionComment.IncorrectTypeHint
	 */
	protected function get_repository( string $class_name ) {
		return $this->get_repository_manager()->get( $class_name );
	}

	/**
	 * Returns and instance of the CampaignRepository.
	 */
	protected function get_campaign_repository(): CampaignRepository {
		return $this->get_repository( CampaignRepository::class );
	}

	/**
	 * Returns and instance of the DonorRepository.
	 */
	protected function get_donor_repository(): DonorRepository {
		return $this->get_repository( DonorRepository::class );
	}

	/**
	 * Returns and instance of the TransactionRepository.
	 */
	protected function get_transaction_repository(): TransactionRepository {
		return $this->get_repository( TransactionRepository::class );
	}

	/**
	 * Returns and instance of the SubscriptionRepository.
	 */
	protected function get_subscription_repository(): SubscriptionRepository {
		return $this->get_repository( SubscriptionRepository::class );
	}
}
