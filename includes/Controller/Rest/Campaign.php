<?php
/**
 * Campaign entity rest route.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Repository\CampaignRepository;

class Campaign extends AbstractRepositoryRestController {

	/**
	 * Campaign rest route constructor.
	 *
	 * @param CampaignRepository $campaign_repository The campaign repository.
	 */
	public function __construct( CampaignRepository $campaign_repository ) {
		$this->rest_base  = 'campaign';
		$this->repository = $campaign_repository;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function add_rest_fields( array $item ): array {
		$item['total'] = $this->repository->get_total( $item );
		return $item;
	}

	/**
	 * Fetching specific campaign needs to be publicly available.
	 */
	protected function can_read_one(): callable {
		return '__return_true';
	}
}
