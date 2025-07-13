<?php
/**
 * Donor entity rest route.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Domain\Entity\BaseEntity;
use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;

/**
 * @extends BaseRepositoryRestController<DonorEntity>
 */
class Donor extends BaseRepositoryRestController {

	/**
	 * Campaign rest route constructor.
	 *
	 * @param DonorRepository $repository The campaign repository.
	 */
	public function __construct( DonorRepository $repository ) {
		$this->rest_base  = 'donor';
		$this->repository = $repository;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function add_rest_fields( BaseEntity $item ): array {
		$item->total = $this->get_repository( TransactionRepository::class )->count_query(
			[
				'donor_id' => $item->id,
			]
		);
		return (array) $item;
	}
}
