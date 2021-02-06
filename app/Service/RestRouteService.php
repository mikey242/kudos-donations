<?php

namespace Kudos\Service;

use WP_REST_Server;

class RestRouteService {

	/**
	 * Namespace used for registering the routes
	 */
	const NAMESPACE = 'kudos/v1';

	/**
	 * The route used for payment webhook
	 */
	const PAYMENT_WEBHOOK = '/payment/webhook';

	/**
	 * New payment route
	 */
	const PAYMENT_CREATE = '/payment/create';

	/**
	 * Rest route used for checking if api key is valid
	 */
	const PAYMENT_TEST = '/payment/test-api';

	/**
	 * Route used to send a test email
	 */
	const EMAIL_TEST = '/email/test';

	/**
	 * @var array[]
	 */
	private $routes;

	/**
	 * RestRoutesService constructor.
	 */
	public function __construct() {

		$this->payment_service();
		$this->mailer_service();

	}

	/**
	 * Payment service routes
	 *
	 * @since 2.3.4
	 */
	private function payment_service() {

		$payment = new PaymentService();

		$this->routes[] = [
			self::PAYMENT_CREATE => [
				'methods'             => 'POST',
				'callback'            => [ $payment, 'submit_payment' ],
				'permission_callback' => '__return_true',
			],

			self::PAYMENT_WEBHOOK => [
				'methods'             => 'POST',
				'callback'            => [ $payment, 'handle_webhook' ],
				'args'                => [
					'id' => [
						'required' => true,
					],
				],
				'permission_callback' => '__return_true',
			],

			self::PAYMENT_TEST => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $payment, 'check_api_keys' ],
				'args'                => [
					'apiMode' => [
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
	 * Mail service routes
	 *
	 * @since 2.3.4
	 */
	private function mailer_service() {

		$mailer = new MailerService();

		$this->routes[] = [
			self::EMAIL_TEST => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $mailer, 'send_test' ],
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
	 * Called to register all the routes defined in this service
	 *
	 * @since 2.3.4
	 */
	public function register_all() {

		foreach ( $this->routes as $service ) {
			foreach ( $service as $key => $route ) {
				register_rest_route( self::NAMESPACE, $key, $route );
			}
		}

	}


}