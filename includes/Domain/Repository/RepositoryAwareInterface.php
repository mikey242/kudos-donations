<?php
/**
 * Adds repository.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Repository;

interface RepositoryAwareInterface {
	/**
	 * Adds the repository manager,
	 *
	 * @param RepositoryManager $repository_manager Instance of repository manager.
	 */
	public function set_repository_manager( RepositoryManager $repository_manager ): void;
}
