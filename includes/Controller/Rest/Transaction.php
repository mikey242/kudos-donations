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

use IseardMedia\Kudos\Repository\CampaignRepository;
use IseardMedia\Kudos\Repository\DonorRepository;
use IseardMedia\Kudos\Repository\TransactionRepository;

class Transaction extends AbstractRepositoryRestController {
	private CampaignRepository $campaigns;
	private DonorRepository $donors;

	/**
	 * Campaign rest route constructor.
	 *
	 * @param TransactionRepository $transactions The transaction repository.
	 * @param CampaignRepository    $campaigns The campaign repository.
	 * @param DonorRepository       $donors The donor repository.
	 */
	public function __construct(
		TransactionRepository $transactions,
		CampaignRepository $campaigns,
		DonorRepository $donors
	) {
		parent::__construct();
		$this->rest_base  = 'transaction';
		$this->repository = $transactions;
		$this->campaigns  = $campaigns;
		$this->donors     = $donors;
	}


	/**
	 * {@inheritDoc}
	 */
	protected function add_rest_fields( array $item ): array {
		if ( ! empty( $item['campaign_id'] ) ) {
			$item['campaign'] = $this->campaigns->find( (int) $item['campaign_id'] );
		}
		if ( ! empty( $item['donor_id'] ) ) {
			$item['donor'] = $this->donors->find( (int) $item['donor_id'] );
		}
		if ( 'paid' === $item['status'] ) {
			$transaction_id = $item['id'];

			$item['invoice_url'] = add_query_arg(
				[
					'kudos_action' => 'view_invoice',
					'_wpnonce'     => wp_create_nonce( "view_invoice_$transaction_id" ),
					'id'           => $transaction_id,
				],
				admin_url( 'edit.php' )
			);
		}
		return $item;
	}
}
