<?php

namespace Kudos\Rest\Route;

use Kudos\Entity\TransactionEntity;
use Kudos\Rest\AbstractRoutes;
use WP_REST_Server;

class TransactionRoutes extends AbstractRoutes {

	/**
	 * Route used to get a single transaction.
	 */
	const GET_TRANSACTION = '/transaction/get/';

	/**
	 * Route used to get all transactions.
	 */
	const GET_ALL = '/transaction/all/';
	/**
	 * @var \Kudos\Service\MapperService
	 */
	protected $mapper_service;

	/**
	 * Transaction routes.
	 */
	public function get_routes(): array {

		$this->mapper_service->get_repository(TransactionEntity::class);

		return [
			self::GET_TRANSACTION . self::ID_PARAMETER => [
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
			self::GET_ALL                         => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_all' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			],
		];
	}

}