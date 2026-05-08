<?php
/**
 * Donor entity rest route.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Domain\Entity\BaseEntity;
use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Enum\PaymentStatus;

/**
 * @extends BaseRepositoryRestController<DonorEntity>
 */
class Donor extends BaseRepositoryRestController {

	protected string $rest_base = 'donor';
	private TransactionRepository $transaction_repository;

	/**
	 * Donor rest route constructor.
	 *
	 * @param DonorRepository       $repository The donor repository.
	 * @param TransactionRepository $transaction_repository The transaction repository.
	 */
	public function __construct( DonorRepository $repository, TransactionRepository $transaction_repository ) {
		$this->repository             = $repository;
		$this->transaction_repository = $transaction_repository;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function prepare_item( BaseEntity $item ): array {
		$item->transaction_count = $this->transaction_repository->count_query(
			[
				'donor_id' => $item->id,
			]
		);
		$item->total             = $this->transaction_repository->sum_query(
			'value',
			[
				'donor_id' => $item->id,
				'status'   => PaymentStatus::PAID,
			]
		);
		return (array) $item;
	}
}
