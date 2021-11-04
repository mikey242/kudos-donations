<?php

namespace Kudos\Rest;

use Kudos\Entity\AbstractEntity;
use Kudos\Service\MapperService;
use WP_REST_Request;

abstract class AbstractRoutes implements RouteInterface {

	const ID_PARAMETER = '(?P<id>\d+)';

	/**
	 * @var \Kudos\Service\MapperService
	 */
	protected $mapper_service;

	/**
	 * AbstractRoutes constructor.
	 */
	public function __construct( MapperService $mapper_service ) {

		$this->mapper_service = $mapper_service;

	}

	/**
	 * Get all routes.
	 */
	abstract public function get_routes(): array;

	/**
	 * Get one by id.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \Kudos\Entity\AbstractEntity|null
	 */
	public function get_one( WP_REST_Request $request): ?AbstractEntity {
		$mapper = $this->mapper_service;

		return $mapper->get_one_by( [
			'id' => $request['id'],
		] );
	}

	/**
	 * Get all records.
	 *
	 * @return array|object|null
	 */
	public function get_all() {
		$mapper = $this->mapper_service;

		return $mapper->get_all_by();
	}

	/**
	 * Get all records.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	public function get_all_between( WP_REST_Request $request ): array {
		$mapper = $this->mapper_service;

		if ( $request->has_valid_params() ) {
			$params = $request->get_query_params();

			if ( ! empty( $params['start'] ) && ! empty( $params['end'] ) ) {
				$start = $params['start'];
				$end   = $params['end'];

				return $mapper->get_all_between( $start, $end );
			}

			return $mapper->get_all_by();
		}

		return [];
	}

}