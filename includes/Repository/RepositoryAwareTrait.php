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
}
