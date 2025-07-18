<?php
/**
 * Transaction entity rest route.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Domain\Entity\BaseEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\BaseRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;

/**
 * @extends BaseRepositoryRestController<TransactionEntity>
 */
class Transaction extends BaseRepositoryRestController {

	/**
	 * @var TransactionRepository
	 */
	protected BaseRepository $repository;

	/**
	 * Campaign rest route constructor.
	 *
	 * @param TransactionRepository $transactions The transaction repository.
	 */
	public function __construct( TransactionRepository $transactions ) {
		$this->rest_base  = 'transaction';
		$this->repository = $transactions;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function add_rest_fields( BaseEntity $item ): array {
		$item->campaign     = $this->repository->get_campaign( $item, [ 'title' ] );
		$item->donor        = $this->repository->get_donor( $item, [ 'name' ] );
		$item->subscription = $this->repository->get_subscription( $item );

		if ( 'paid' === $item->status ) {
			$transaction_id = $item->id;

			$item->invoice_url = add_query_arg(
				[
					'kudos_action' => 'view_invoice',
					'_wpnonce'     => wp_create_nonce( "view_invoice_$transaction_id" ),
					'id'           => $transaction_id,
				],
				admin_url( 'admin.php' )
			);
		}
		return (array) $item;
	}
}
