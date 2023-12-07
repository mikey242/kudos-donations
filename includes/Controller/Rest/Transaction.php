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
			''         => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_all' ],
				'permission_callback' => '__return_true',
			],
			'/get'     => [
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
			'/between' => [
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
			TransactionPostType::get_posts(
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

			$response->set_data( TransactionPostType::get_posts() );

			return $response;
		}

		return $response;
	}
}
