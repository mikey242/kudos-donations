<?php

namespace Kudos\Service;

use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use WP_REST_Request;

class MailerService extends AbstractService {

	/**
	 * @var bool|mixed|void
	 */
	private $from;

	/**
	 * Mailer constructor.
	 *
	 * @since    1.1.0
	 */
	public function __construct() {

		$from_name = apply_filters('kudos_email_from_name', __("Kudos Donations", 'kudos-donations'));
		$this->from = "From: $from_name " . ' <' . ( Settings::get_setting( 'smtp_from' ) ?: Settings::get_setting( 'smtp_username' ) ) . '>';
		parent::__construct();

	}

	/**
	 * Initializes the mailer by modifying default config if setting
	 * is enabled.
	 *
	 * @param PHPMailer $phpmailer
	 *
	 * @throws Exception;
	 * @since    1.1.0
	 */
	public function init( PHPMailer $phpmailer ) {

		// Toggle this on to enable PHPMailer's debug mode
		$phpmailer->SMTPDebug = 0;

		// Add logo as attachment
		$phpmailer->addEmbeddedImage( Utils::get_asset_url( 'img/logo-colour-40.png', true ),
			'kudos-logo',
			'kudos-logo.png' );

		// Add custom config if enabled
		if ( Settings::get_setting( 'smtp_enable' ) ) {
			$phpmailer->isSMTP();
			$phpmailer->isHTML( true );
			$phpmailer->Host        = Settings::get_setting( 'smtp_host' );
			$phpmailer->SMTPAutoTLS = Settings::get_setting( 'smtp_autotls' );
			$phpmailer->SMTPAuth    = true;
			$phpmailer->SMTPSecure  = Settings::get_setting( 'smtp_encryption' );
			$phpmailer->Username    = Settings::get_setting( 'smtp_username' );
			$phpmailer->Password    = Settings::get_setting( 'smtp_password' );
			$phpmailer->Port        = Settings::get_setting( 'smtp_port' );
		}

	}

	/**
	 * Sends receipt to the donor
	 *
	 * @param TransactionEntity $transaction
	 *
	 * @since    1.1.0
	 */
	public function send_receipt( TransactionEntity $transaction ) {

		// Check if setting enabled
		if ( ! Settings::get_setting( 'email_receipt_enable' ) ) {
			return;
		}

		$bcc = Settings::get_setting( 'email_bcc' );

		$headers[] = $this->from;
		if ( filter_var( $bcc, FILTER_SANITIZE_EMAIL ) ) {
			$headers[] = "bcc: " . Settings::get_setting( 'email_bcc' );
		}

		// Assign attachment
		$attachments = apply_filters( 'kudos_receipt_attachment', [], $transaction->order_id );

		// Create array of variables for use in twig template
		$renderArray = [
			'name'         => $transaction->get_donor()->name ?? '',
			'date'         => $transaction->created,
			'description'  => Utils::get_sequence_type( $transaction->sequence_type ),
			'amount'       => ( ! empty( $transaction->currency ) ? html_entity_decode( Utils::get_currency_symbol( $transaction->currency ) ) : '' ) . number_format_i18n( $transaction->value,
					2 ),
			'receipt_id'   => $transaction->order_id,
			'website_name' => get_bloginfo( 'name' ),
		];

		// Add a cancel subscription url if transaction associated with a subscription
		if ( ! empty( $transaction->subscription_id ) ) {
			$mapper = new MapperService();
			$donor  = $transaction->get_donor();
			$secret = $donor->create_secret( '+1 week' );
			$mapper->save( $donor );
			$subscription_id           = $transaction->subscription_id;
			$token                     = password_hash( $secret, PASSWORD_DEFAULT );
			$cancel_url                = get_home_url();
			$cancel_url                = add_query_arg( 'kudos_token', $token, $cancel_url );
			$cancel_url                = add_query_arg( 'kudos_subscription_id',
				base64_encode( $subscription_id ),
				$cancel_url );
			$renderArray['cancel_url'] = $cancel_url;
		}

		$twig = new TwigService();
		$body = $twig->render( 'emails/receipt.html.twig', $renderArray );

		$this->send( $transaction->get_donor()->email,
			__( 'Kudos Donation Receipt', 'kudos-donations' ),
			$body,
			$headers,
			$attachments );
	}

	/**
	 * Email send function
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string $body
	 * @param array $headers
	 * @param array $attachment
	 *
	 * @return bool
	 * @since    1.1.0
	 */
	private function send( string $to, string $subject, string $body, array $headers = [], array $attachment = [] ) {

		// Use hook to modify existing config
		add_action( 'phpmailer_init', [ $this, 'init' ] );
		$mail = wp_mail( $to, $subject, $body, $headers, $attachment );

		if ( $mail ) {
			$this->logger->info( sprintf( 'Email with subject "%s" sent to "%s"',
				$subject,
				$to ) );
		} else {
			$this->logger->error( sprintf( 'Email with subject "%s" failed to be sent to "%s"',
				$subject,
				$to ) );
		}

		// Remove action to prevent conflict
		remove_action( 'phpmailer_init', [ $this, 'init' ] );

		return $mail;

	}

	/**
	 * Sends a test email using send_message
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool
	 * @since    1.1.0
	 */
	public function send_test( WP_REST_Request $request ) {

		if ( empty( $request['email'] ) ) {
			wp_send_json_error( __( 'Please provide an email address.', 'kudos-donations' ) );
		}

		$email   = sanitize_email( $request['email'] );
		$header  = __( 'It worked!', 'kudos-donations' );
		$message = __( 'Looks like your email settings are set up correctly :-)', 'kudos-donations' );

		$result = $this->send_message( $email, $header, $message );

		if ( $result ) {
			/* translators: %s: API mode */
			wp_send_json_success( sprintf( __( "Email sent to %s.", 'kudos-donations' ), $email ) );
		} else {
			/* translators: %s: API mode */
			wp_send_json_error( __( "Error sending email, please check the settings and try again.",
				'kudos-donations' ) );
		}

		return $result;

	}

	/**
	 * Sends a message using the message template
	 *
	 * @param string $email
	 * @param string $header
	 * @param string $message
	 *
	 * @return bool
	 * @since   2.0.0
	 */
	public function send_message( string $email, string $header, string $message ) {

		$twig = new TwigService();
		$body = $twig->render( 'emails/message.html.twig',
			[
				'header'       => $header,
				'message'      => $message,
				'website_name' => get_bloginfo( 'name' ),
			] );

		$headers[] = $this->from;

		return $this->send( $email, $header, $body, $headers );
	}

}