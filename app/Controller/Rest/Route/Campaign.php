<?php

namespace Kudos\Controller\Rest\Route;

use Exception;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Campaign extends Base {

	/**
	 * Base route.
	 */
	protected $base = 'campaign';

	/**
	 * Transaction routes.
	 */
	public function get_routes(): array {

		return [
			$this->get_base() . '/get' => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_one' ],
				'args'                => [
					'id' => [
						'type'     => 'string',
						'required' => true,
					],
				],
				'permission_callback' => '__return_true',
			],
		];
	}

	/**
	 * Get one by id.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_one( WP_REST_Request $request ): WP_REST_Response {

		if ( $request->has_valid_params() ) {
			$params = $request->get_query_params();

			try {
				return new WP_REST_Response( \Kudos\Helpers\CustomPostType::get_post( $params['id'] ) );

			} catch ( Exception $e ) {
				wp_send_json_error( $e->getMessage() );
			}
		}

		return new WP_REST_Response( '' );

	}
}