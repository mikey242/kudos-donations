<?php

namespace Kudos\Service;

use WP_REST_Server;

class RestService {

	public const NAMESPACE = 'kudos/v1';

	/**
	 * Array of routes and config ['route' => 'config']
	 *
	 * @var array
	 */
	private $routes;
	/**
	 * Our Mailer service
	 *
	 * @var false|MailerService
	 */
	private $mailer;
	/**
	 * Our Mollie service
	 *
	 * @var false|MollieService
	 */
	private $mollie;

	/**
	 * Rest service constructor.
	 *
	 * @since    2.3.0
	 */
	public function __construct() {

		$this->mailer    = MailerService::factory();
		$this->mollie    = MollieService::factory();

		$this->routes    = [

			'mollie/payment/create' => [
				'methods'             => 'POST',
				'callback'            => [ $this->mollie, 'submit_payment' ],
				'args'                => [
					'form' => [
						'required' => true,
					],
				],
				'permission_callback' => '__return_true',
			],

			'mollie/payment/webhook' => [
				'methods'             => 'POST',
				'callback'            => [ $this->mollie, 'rest_api_mollie_webhook' ],
				'args'                => [
					'id' => [
						'required' => true,
					],
				],
				'permission_callback' => '__return_true',
			],

			'mollie/check-api' => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this->mollie, 'check_api_keys' ],
				'args'                => [
					'apiMode' => [
						'required' => true,
					],
				],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			],

			'email/test' => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this->mailer, 'send_test' ],
				'args'                => [
					'email' => [
						'required' => true,
					],
				],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			],
		];

	}

	/**
	 * Registers routes contained in te routes property
	 *
	 * @return void
	 * @since   2.3.0
	 */
	public function register_routes() {

		foreach ( $this->routes as $key => $route ) {
			register_rest_route( self::NAMESPACE, $key, $route );
		}

	}
}
