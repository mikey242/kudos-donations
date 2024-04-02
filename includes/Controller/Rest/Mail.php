<?php
/**
 * Mail Rest Routes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Service\MailerService;
use WP_REST_Server;

class Mail extends AbstractRestController {

	/**
	 * Mailer service.
	 *
	 * @var MailerService
	 */
	private MailerService $mailer_service;

	/**
	 * PaymentRoutes constructor.
	 *
	 * @param MailerService $mailer_service Mailer service.
	 */
	public function __construct( MailerService $mailer_service ) {
		parent::__construct();

		$this->rest_base      = 'email';
		$this->mailer_service = $mailer_service;
	}

	/**
	 * Mail service routes.
	 */
	public function get_routes(): array {
		$mailer = $this->mailer_service;

		return [
			'/test' => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $mailer, 'send_test' ],
				'args'                => [
					'email' => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_email',
					],
				],
				'permission_callback' => [ $this, 'can_manage_options' ],
			],
		];
	}
}
