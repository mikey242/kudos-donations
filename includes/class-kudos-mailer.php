<?php

namespace Kudos;

use PHPMailer;
use WP_REST_Request;
use WP_REST_Server;

class Kudos_Mailer
{

	/**
	 * @var Kudos_Logger
	 */
	private $logger;
	/**
	 * @var Kudos_Invoice
	 */
	private $invoice;

	/**
	 * Kudos_Mailer constructor.
	 *
	 * @since    1.1.0
	 */
	public function __construct() {
		$this->logger = new Kudos_Logger();
		$this->invoice = new Kudos_Invoice();
	}

	/**
	 * Initializes the mailer by modifying default config if setting
	 * is enabled.
	 *
	 * @since    1.1.0
	 * @param PHPMailer $phpmailer
	 */
	public function init($phpmailer) {

		$custom_smtp = get_option('_kudos_smtp_enable');

		$phpmailer->SMTPDebug = (WP_DEBUG ? 1 : 0); //Alternative to above constant

		// Add logo as attachment
		$phpmailer->addEmbeddedImage(get_asset_url('img/logo-colour-40.png', true), 'kudos-logo', 'kudos-logo.png');

		// Add custom config if enabled
		if($custom_smtp) {
			$phpmailer->isSMTP();
			$phpmailer->isHTML(true);
			$phpmailer->Host = get_option('_kudos_smtp_host');
			$phpmailer->SMTPAutoTLS = get_option('_kudos_smtp_autotls');
			$phpmailer->SMTPAuth = true;
			$phpmailer->SMTPSecure = get_option('_kudos_smtp_encryption');
			$phpmailer->Username = get_option('_kudos_smtp_username');
			$phpmailer->Password = get_option('_kudos_smtp_password');
			$phpmailer->Port = get_option('_kudos_smtp_port');
		}

	}

	/**
	 * Sends receipt to the customer
	 *
	 * @since    1.1.0
	 * @param object $transaction
	 */
	public function send_receipt($transaction) {

		// Check if setting enabled
		if(!get_option('_kudos_email_receipt_enable')) {
			return;
		}

		$bcc = get_option('_kudos_email_bcc');

		$headers[] = "From: Kudos Donations <wordpress@iseard.media>";
		if(filter_var($bcc, FILTER_SANITIZE_EMAIL)) {
			$headers[] = "bcc: " . get_option('_kudos_email_bcc');
		}

		// Get invoice if option enabled
		$attachment = (get_option('_kudos_attach_invoice') ? $this->invoice->get_invoice($transaction->order_id, true) : null);

		// Create array of variables for use in twig template
		$renderArray = [
			'name' => !empty($transaction->name) ? $transaction->name : '',
			'date' => $transaction->time,
			'description' => get_sequence_type($transaction->sequence_type),
			'amount' => (!empty($transaction->currency) ? html_entity_decode(get_currency_symbol($transaction->currency)) : '') . number_format_i18n($transaction->value, 2),
			'receipt_id' => $transaction->order_id,
			'website_name' => get_bloginfo('name'),
		];

		// Add a cancel subscription url if transaction associated with a subscription
		if(!empty($transaction->subscription_id)) {
			$subscription_id = $transaction->subscription_id;
			$token = password_hash($transaction->customer_id, PASSWORD_DEFAULT);
			$cancel_url = get_home_url();
			$cancel_url = add_query_arg('kudos_token', $token, $cancel_url);
			$cancel_url = add_query_arg('kudos_subscription_id', base64_encode($subscription_id), $cancel_url);
			$renderArray['cancel_url'] = $cancel_url;
		}

		$twig = new Kudos_Twig();
		$body = $twig->render('emails/invoice.html.twig', $renderArray);

		self::send($transaction->email, __('Kudos Donation Receipt', 'kudos-donations'), $body, $headers, [$attachment]);
	}

	/**
	 * Sends a test email to specified email address
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool
	 * @since    1.1.0
	 */
	public function send_test(WP_REST_Request $request) {

		if(empty($request['email'])) {
			wp_send_json_error(__('Please provide an email address.', 'kudos_donations'));
		}

		$email = sanitize_email($request['email']);

		$twig = new Kudos_Twig();
		$body = $twig->render('emails/test.html.twig', ['website_name' => get_bloginfo('name')]);

		$headers = [
			"From: Kudos Donations <wordpress@iseard.media>"
		];

		$result = self::send($email, __('Test email', 'kudos-donations'), $body, $headers);

		if($result) {
			/* translators: %s: API mode */
			wp_send_json_success(sprintf(__("Email sent to %s.", 'kudos-donations'), $email));
		} else {
			/* translators: %s: API mode */
			wp_send_json_error( __("Error sending email, please check the settings and try again.", 'kudos-donations'));
		}

		return $result;
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
	private function send($to, $subject, $body, $headers=[], $attachment=[]) {

		// Use hook to modify existing config
		add_action('phpmailer_init', [$this, 'init']);
		$mail = wp_mail($to, $subject, $body, $headers, $attachment);

		if($mail) {
			$this->logger->log(sprintf(__('Email with subject "%s" sent to "%s"', 'kudos_donations'), $subject, $to));
		} else {
			$this->logger->log(sprintf(__('Email with subject "%s" failed to be sent to "%s"', 'kudos_donations'), $subject, $to), 'WARNING');
		}

		// Remove action to prevent conflict
		remove_action('phpmailer_init', [$this, 'init']);

		return $mail;

	}

	/**
	 * REST route for checking API keys
	 *
	 * @since   1.1.0
	 */
	public function register_send_test_email() {
		register_rest_route('kudos/v1', 'email/test', [
			'methods'   => WP_REST_Server::CREATABLE,
			'callback'  => [$this, 'send_test'],
			'args' => [
				'email' => [
					'required' => true
				]
			]
		]);
	}

}