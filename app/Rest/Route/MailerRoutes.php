<?php

namespace Kudos\Rest\Route;

use Kudos\Rest\RouteInterface;
use Kudos\Service\MailerService;
use WP_REST_Server;

class MailerRoutes implements RouteInterface {

	/**
	 * Route used to send a test email.
	 */
	const EMAIL_TEST = '/email/test';

	/**
	 * @var \Kudos\Service\MailerService
	 */
	private $mailer_service;

	/**
	 * PaymentRoutes constructor.
	 */
	public function __construct(MailerService $mailer_service) {

		$this->mailer_service = $mailer_service;

	}

	/**
	 * Mail service routes.
	 */
	public function get_routes(): array {

		$mailer = $this->mailer_service;

		return [
			self::EMAIL_TEST => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $mailer, 'send_test' ],
				'args'                => [
					'email' => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_email',
					],
				],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			],
		];
	}
	
}