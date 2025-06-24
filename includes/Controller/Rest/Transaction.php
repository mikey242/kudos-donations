<?php
/**
 * Transaction entity rest route.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Repository\TransactionRepository;

class Transaction extends AbstractRepositoryRestController {
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
	protected function add_rest_fields( array $item ): array {
		$item['campaign']     = $this->repository->get_campaign( $item, [ 'title' ] );
		$item['donor']        = $this->repository->get_donor( $item, [ 'name' ] );
		$item['subscription'] = $this->repository->get_subscription( $item );

		if ( 'paid' === $item['status'] ) {
			$transaction_id = $item['id'];

			$item['invoice_url'] = add_query_arg(
				[
					'kudos_action' => 'view_invoice',
					'_wpnonce'     => wp_create_nonce( "view_invoice_$transaction_id" ),
					'id'           => $transaction_id,
				],
				admin_url( 'admin.php' )
			);
		}
		return $item;
	}
}
