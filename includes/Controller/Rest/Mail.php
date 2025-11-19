<?php
/**
 * Mail Rest Routes.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Service\MailerService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Mail extends BaseRestController {

	/**
	 * Mailer service.
	 *
	 * @var MailerService
	 */
	private MailerService $mailer;

	private string $error_message = '';

	/**
	 * PaymentRoutes constructor.
	 *
	 * @param MailerService $mailer Mailer service.
	 */
	public function __construct( MailerService $mailer ) {
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
		$raw_email = $request['email'];
		$email     = \is_string( $raw_email ) ? sanitize_email( $raw_email ) : null;

		if ( null === $email ) {
			return new WP_REST_Response( [ 'message' => __( 'Invalid test email address', 'kudos-donations' ) ] );
		}

		$header  = __( 'It worked!', 'kudos-donations' );
		$message = __( 'Looks like your email settings are set up correctly :-)', 'kudos-donations' );

		add_action( 'wp_mail_failed', [ $this, 'handle_error' ] );
		$result = $this->mailer->send_message( $email, $header, $message );
		remove_action( 'wp_mail_failed', [ $this, 'handle_error' ] );

		if ( true === $result ) {
			/* translators: %s: API mode */
			return new WP_REST_Response( [ 'message' => \sprintf( __( 'Email sent to %s.', 'kudos-donations' ), $email ) ], 200 );
		}
		/* translators: %s: The error returned by wp_mailer */
		return new WP_REST_Response( [ 'message' => \sprintf( __( 'Something went wrong sending the test email: %s', 'kudos-donations' ), $this->error_message ) ], 500 );
	}

	/**
	 * Sets the error_message property.
	 *
	 * @param WP_Error $error The WordPress error object.
	 */
	public function handle_error( WP_Error $error ) {
		$this->error_message = $error->get_error_message();
	}
}
