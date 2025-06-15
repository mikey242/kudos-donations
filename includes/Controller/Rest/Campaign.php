<?php

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Repository\CampaignRepository;
use IseardMedia\Kudos\Repository\TransactionRepository;

class Campaign extends AbstractRepositoryRestController {

	private TransactionRepository $transaction_repository;

	/**
	 * Campaign rest route constructor.
	 *
	 * @param CampaignRepository    $campaign_repository The campaign repository.
	 * @param TransactionRepository $transaction_repository The transaction repository.
	 */
	public function __construct( CampaignRepository $campaign_repository, TransactionRepository $transaction_repository ) {
		parent::__construct();
		$this->rest_base              = 'campaign';
		$this->repository             = $campaign_repository;
		$this->transaction_repository = $transaction_repository;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function add_rest_fields( array $item ): array {
		$item['total'] = $this->transaction_repository->get_total_by_campaign_id( (int) $item['id'] );
		return $item;
	}
}
