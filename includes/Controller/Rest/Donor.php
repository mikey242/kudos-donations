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

use IseardMedia\Kudos\Entity\DonorEntity;
use IseardMedia\Kudos\Repository\DonorRepository;

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
	protected function add_rest_fields( $item ): array {
		$item->total = $this->get_transaction_repository()->get_total_by_donor_id( $item->id );
		return (array) $item;
	}
}
