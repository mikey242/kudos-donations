<?php
/**
 * Repository manager.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Repository;

use IseardMedia\Kudos\Domain\Entity\BaseEntity;

class RepositoryManager {

	/**
	 * Array of repositories.
	 *
	 * @var array<class-string, BaseRepository>
	 */
	protected array $repositories = [];

	/**
	 * Receives the repositories.
	 *
	 * @param iterable<BaseRepository> $repositories Array of repositories.
	 *
	 * @phpcs:disable Squiz.Commenting.FunctionComment.IncorrectTypeHint
	 */
	public function __construct( iterable $repositories ) {
		foreach ( $repositories as $service ) {
			$this->add( $service );
		}
	}

	/**
	 * Add repository to list.
	 *
	 * @param BaseRepository $service Service.
	 */
	public function add( BaseRepository $service ): void {
		$this->repositories[ \get_class( $service ) ] = $service;
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
		if ( ! isset( $this->repositories[ $class_name ] ) ) {
			throw new \RuntimeException( esc_attr( "Repository not registered: $class_name" ) );
		}

		/** @var TRepository */
		return $this->repositories[ $class_name ];
	}
}
