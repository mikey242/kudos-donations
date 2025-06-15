<?php
/**
 * Mail Rest Routes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Service\MailerService;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Mail extends AbstractRestController {

	/**
	 * Mailer service.
	 *
	 * @var MailerService
	 */
	private MailerService $mailer;

	/**
	 * PaymentRoutes constructor.
	 *
	 * @param MailerService $mailer Mailer service.
	 */
	public function __construct( MailerService $mailer ) {
		parent::__construct();

		$this->rest_base = 'email';
		$this->mailer    = $mailer;
	}

	/**
	 * Mail service routes.
	 */
	public function get_routes(): array {

		return [
			'/test' => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'send_test' ],
				'args'                => [
					'email' => [
						'type'              => FieldType::STRING,
						'required'          => true,
						'sanitize_callback' => 'sanitize_email',
					],
				],
				'permission_callback' => [ $this, 'can_manage_options' ],
			],
		];
	}

	/**
	 * Sends a test email using send_message.
	 *
	 * @param WP_REST_Request $request Request array.
	 */
	public function send_test( WP_REST_Request $request ): WP_REST_Response {
		$email = sanitize_email( $request['email'] );

		if ( ! $email ) {
			return new WP_REST_Response( [ 'message' => __( 'Invalid test email address', 'kudos-donations' ) ] );
		}

		$header  = __( 'It worked!', 'kudos-donations' );
		$message = __( 'Looks like your email settings are set up correctly :-)', 'kudos-donations' );

		$result = $this->mailer->send_message( $email, $header, $message );

		if ( $result ) {
			/* translators: %s: API mode */
			return new WP_REST_Response( [ 'message' => \sprintf( __( 'Email sent to %s.', 'kudos-donations' ), $email ) ], 200 );
		}

		return new WP_REST_Response( [ 'message' => __( 'Something went wrong sending the test email', 'kudos-donations' ) ], 500 );
	}
}
