<?php
/**
 * Transaction Rest Routes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Transaction extends AbstractRestController {

	/**
	 * Route constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->rest_base = 'transaction';
	}

	/**
	 * TransactionPostType routes.
	 */
	public function get_routes(): array {
		return [
			''                                     => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_all' ],
				'permission_callback' => '__return_true',
			],
			'/get'                                 => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_one' ],
				'args'                => [
					'id' => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'absint',
					],
				],
				'permission_callback' => '__return_true',
			],
			'/between'                             => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_all_between' ],
				'args'                => [
					'start' => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_title',
					],
					'end'   => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_title',
					],
				],
				'permission_callback' => '__return_true',
			],
			'/campaign/total/(?P<campaign_id>\d+)' => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_total_campaign' ],
				'args'                => [
					'campaign_id' => [
						'type'              => 'int',
						'required'          => true,
						'sanitize_callback' => 'absint',
					],
				],
				'permission_callback' => '__return_true',
			],
			'/donor/total/'                        => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_donor_total' ],
				'args'                => [
					'donor_id' => [
						'type'              => 'int',
						'required'          => true,
						'sanitize_callback' => 'absint',
					],
				],
				'permission_callback' => [ $this, 'can_edit_posts' ],
			],
		];
	}

	/**
	 * Get one by id.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function get_one( WP_REST_Request $request ): WP_REST_Response {
		return new WP_REST_Response(
			get_post( $request['id'] )
		);
	}

	/**
	 * Get all records.
	 */
	public function get_all(): WP_REST_Response {
		return new WP_REST_Response(
			TransactionPostType::get_by_meta(
				[ TransactionPostType::META_FIELD_STATUS => PaymentStatus::PAID ]
			)
		);
	}

	/**
	 * Get all records between specified dates.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function get_all_between( WP_REST_Request $request ): WP_REST_Response {
		$response = new WP_REST_Response();

		if ( $request->has_valid_params() ) {
			$params = $request->get_query_params();
			if ( ! empty( $params['start'] ) && ! empty( $params['end'] ) ) {
				$start = $params['start'] . ' 00:00:00';
				$end   = $params['end'] . ' 23:59:59';

				$response->set_data( TransactionPostType::get_all_between( $start, $end ) );

				return $response;
			}

			$response->set_data( TransactionPostType::get_all() );

			return $response;
		}

		return $response;
	}

	/**
	 * Returns the total paid donations for a campaign.
	 *
	 * @param WP_REST_Request $request Request object.
	 */
	public function get_total_campaign( WP_REST_Request $request ): WP_REST_Response {
		$response = new WP_REST_Response();

		if ( $request->has_valid_params() ) {
			$campaign_id = $request->get_param( 'campaign_id' );
			$status      = $request->get_param( 'status' );
			if ( ! empty( $campaign_id ) ) {
				$transactions = TransactionPostType::get_by_meta(
					[
						TransactionPostType::META_FIELD_CAMPAIGN_ID => $campaign_id,
						TransactionPostType::META_FIELD_STATUS      => $status ?? PaymentStatus::PAID,
					]
				);

				$values = array_column( $transactions, TransactionPostType::META_FIELD_VALUE );
				$total  = array_sum( $values );

				$additional_funds = get_post_meta( $campaign_id, CampaignPostType::META_FIELD_ADDITIONAL_FUNDS, true );

				$total = $total + (int) $additional_funds;
				$response->set_data( $total );
			}
		}

		return $response;
	}

	/**
	 * Gets total donations by donor.
	 *
	 * @param WP_REST_Request $request Request object.
	 */
	public function get_donor_total( WP_REST_Request $request ): WP_REST_Response {
		$response = new WP_REST_Response();

		if ( $request->has_valid_params() ) {
			$donor_id = $request->get_param( 'donor_id' );
			if ( ! empty( $donor_id ) ) {
				$transactions = TransactionPostType::get_by_meta(
					[
						TransactionPostType::META_FIELD_DONOR_ID => $donor_id,
						TransactionPostType::META_FIELD_STATUS   => PaymentStatus::PAID,
					]
				);

				$values = array_column( $transactions, TransactionPostType::META_FIELD_VALUE );
				$total  = array_sum( $values );

				$response->set_data( $total );
			}
		}

		return $response;
	}
}