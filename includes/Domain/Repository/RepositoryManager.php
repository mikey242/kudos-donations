<?php
/**
 * Repository manager.
 *
 * @link https://code.iseard.media/michael/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Repository;

use IseardMedia\Kudos\Domain\Entity\BaseEntity;
use IseardMedia\Kudos\ThirdParty\Symfony\Component\DependencyInjection\ServiceLocator;

class RepositoryManager {

	/**
	 * Container locator for the repositories, keyed by class name. Each repository is only
	 * built when first requested, rather than all of them whenever the manager is built.
	 *
	 * @var ServiceLocator
	 */
	private ServiceLocator $repositories;

	/**
	 * Receives the repositories.
	 *
	 * @param ServiceLocator $repositories Locator for the tagged repositories.
	 */
	public function __construct( ServiceLocator $repositories ) {
		$this->repositories = $repositories;
	}

	/**
	 * Gets the requested repository.
	 *
	 * @throws \RuntimeException Thrown if repository not found.
	 *
	 * @param class-string<TRepository> $class_name Repository class name.
	 * @return TRepository
	 *
	 * @template TEntity of BaseEntity
	 * @template TRepository of BaseRepository<TEntity>
	 *
	 * @phpcs:disable Squiz.Commenting.FunctionComment.IncorrectTypeHint
	 */
	public function get( string $class_name ) {
		if ( ! $this->repositories->has( $class_name ) ) {
			throw new \RuntimeException( esc_attr( "Repository not registered: $class_name" ) );
		}

		/** @var TRepository */
		return $this->repositories->get( $class_name );
	}
}
