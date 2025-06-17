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

use IseardMedia\Kudos\Repository\DonorRepository;
use IseardMedia\Kudos\Repository\TransactionRepository;

class Donor extends AbstractRepositoryRestController {

	private TransactionRepository $transaction_repository;

	/**
	 * Campaign rest route constructor.
	 *
	 * @param DonorRepository       $repository The campaign repository.
	 * @param TransactionRepository $transaction_repository The transaction repository.
	 */
	public function __construct( DonorRepository $repository, TransactionRepository $transaction_repository ) {
		parent::__construct();
		$this->rest_base              = 'donor';
		$this->repository             = $repository;
		$this->transaction_repository = $transaction_repository;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function add_rest_fields( array $item ): array {
		$item['total'] = $this->transaction_repository->get_total_by_donor_id( (int) $item['id'] );
		return $item;
	}
}
