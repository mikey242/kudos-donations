<?php

namespace Kudos\Rest;

use Kudos\Service\MapperService;

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
	 * @param $data
	 *
	 * @return false|string
	 */
	public function get_one( $data ) {
		$mapper = $this->mapper_service;

		return $mapper->get_one_by( [
			'id' => $data['id'],
		] );
	}

	/**
	 * Get all records.
	 *
	 * @return false|string
	 */
	public function get_all() {
		$mapper = $this->mapper_service;

		return $mapper->get_all_by();
	}

}