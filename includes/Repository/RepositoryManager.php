<?php
/**
 * Repository manager.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Repository;

class RepositoryManager {

	/**
	 * Array of repositories.
	 *
	 * @var RepositoryInterface[]
	 */
	protected array $repositories = [];

	/**
	 * Receives the repositories.
	 *
	 * @param iterable $repositories Array of repositories.
	 */
	public function __construct( iterable $repositories ) {
		foreach ( $repositories as $service ) {
			$this->add( $service );
		}
	}

	/**
	 * Add repository to list.
	 *
	 * @param RepositoryInterface $service Service.
	 */
	public function add( RepositoryInterface $service ): void {
		$class_name                        = \get_class( $service );
		$this->repositories[ $class_name ] = $service;
	}

	/**
	 * Gets the requested repository.
	 *
	 * @throws \RuntimeException Thrown if repository not found.
	 *
	 * @param string $class_name FQCN for repository.
	 */
	public function get( string $class_name ): RepositoryInterface {
		if ( ! isset( $this->repositories[ $class_name ] ) ) {
			throw new \RuntimeException( esc_attr( "Repository not registered: $class_name" ) );
		}

		return $this->repositories[ $class_name ];
	}
}
