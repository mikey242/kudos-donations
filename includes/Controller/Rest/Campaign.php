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

use IseardMedia\Kudos\Repository\BaseRepository;
use IseardMedia\Kudos\Repository\CampaignRepository;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class Campaign extends BaseRepositoryRestController {

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
	 * Get a single entity by ID.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( WP_REST_Request $request ) {
		$id = (int) $request->get_param( 'id' );

		// Try to find the entity by id and if none found assume it is the wp_post_id or wp_post_slug from earlier versions.
		$item = $this->repository->find( $id ) ??
				$this->repository->find_one_by( [ BaseRepository::POST_ID => $id ] ) ??
				$this->repository->find_one_by( [ CampaignRepository::POST_SLUG => $id ] );

		if ( ! $item ) {
			// translators: %s is the entity type singular name (e.g Transaction).
			return new WP_Error( 'not_found', \sprintf( __( '%s not found.', 'kudos-donations' ), $this->repository::get_singular_name() ), [ 'status' => 404 ] );
		}

		return new WP_REST_Response( $this->add_rest_fields( $item ), 200 );
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
