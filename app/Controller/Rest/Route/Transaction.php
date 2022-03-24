<?php

namespace Kudos\Controller\Rest\Route;

use Kudos\Entity\TransactionEntity;
use Kudos\Service\MapperService;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Transaction extends Base {

	/**
	 * Base route.
	 */
	protected $base = 'transaction';

	/**
	 * @var MapperService
	 */
	protected $mapper_service;

	/**
	 * Route constructor.
	 *
	 * @param MapperService $mapper_service
	 */
	public function __construct( MapperService $mapper_service ) {
		$this->mapper_service = $mapper_service;
	}

	/**
	 * Transaction routes.
	 */
	public function get_routes(): array {

		$this->mapper_service->get_repository( TransactionEntity::class );

		return [
			$this->get_base() . '/get'                               => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_one' ],
				'args'                => [
					'id' => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'absint',
					],
				],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			],
			$this->get_base() . '/all'                               => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_all' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			],
			$this->get_base() . '/all/between'                       => [
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
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			],
			$this->get_base() . '/all/campaign/(?P<campaign_id>\w+)' => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_all_campaign' ],
				'args'                => [
					'campaign_id' => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_title',
					],
				],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
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

		$mapper = $this->mapper_service;

		return new WP_REST_Response( $mapper->get_one_by( [
			'id' => $request['id'],
		] ) );
	}

	/**
	 * Get all records.
	 *
	 * @return WP_REST_Response
	 */
	public function get_all(): WP_REST_Response {

		$mapper = $this->mapper_service;

		return new WP_REST_Response( $mapper->get_all_by( [
			'status' => 'paid',
		] ) );
	}

	/**
	 * Get all records between specified dates.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_all_between( WP_REST_Request $request ): WP_REST_Response {

		$mapper   = $this->mapper_service;
		$response = new WP_REST_Response();

		if ( $request->has_valid_params() ) {
			$params = $request->get_query_params();
			if ( ! empty( $params['start'] ) && ! empty( $params['end'] ) ) {
				$start = $params['start'] . ' 00:00:00';
				$end   = $params['end'] . ' 23:59:59';

				$response->set_data( $mapper->get_all_between( $start, $end ) );

				return $response;
			}

			$response->set_data( $mapper->get_all_by() );

			return $response;
		}

		return $response;
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function get_all_campaign( WP_REST_Request $request ): WP_REST_Response {
		$mapper   = $this->mapper_service;
		$response = new WP_REST_Response();
		if ( $request->has_valid_params() ) {
			$param = $request->get_param( 'campaign_id' );
			if ( ! empty( $param ) ) {
				$response->set_data( $mapper->get_all_by( [ 'campaign_id' => $param ] ) );

				return $response;
			}
		}

		return $response;
	}
}