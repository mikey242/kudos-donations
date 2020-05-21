<?php

namespace Kudos;

use PHPMailer;

class Kudos_Mailer
{

	/**
	 * @var Kudos_Logger
	 */
	private $logger;

	/**
	 * Kudos_Mailer constructor.
	 *
	 * @since    1.1.0
	 */
	public function __construct() {
		$this->logger = new Kudos_Logger();
	}

	/**
	 * Initializes the mailer by modifying default config if setting
	 * is enabled.
	 *
	 * @since    1.1.0
	 * @param PHPMailer $phpmailer
	 */
	public function init($phpmailer) {

		$custom_smtp = carbon_get_theme_option('kudos_smtp_enable');

		// Add logo as attachment
		$phpmailer->addEmbeddedImage(get_asset_url('img/logo-colour-40.png', true), 'kudos-logo', 'kudos-logo.png');

		// Add custom config if enabled
		if($custom_smtp) {
			$phpmailer->isSMTP();
			$phpmailer->isHTML(true);
			$phpmailer->Host = carbon_get_theme_option('kudos_smtp_host');
			$phpmailer->SMTPAutoTLS = carbon_get_theme_option('kudos_smtp_autotls');
			$phpmailer->SMTPAuth = true;
			$phpmailer->SMTPSecure = carbon_get_theme_option('kudos_smtp_encryption');
			$phpmailer->Username = carbon_get_theme_option('kudos_smtp_username');
			$phpmailer->Password = carbon_get_theme_option('kudos_smtp_password');
			$phpmailer->Port = carbon_get_theme_option('kudos_smtp_port');
		}

	}

	/**
	 * Sends an invoice to the customer
	 *
	 * @since    1.1.0
	 * @param object $transaction
	 */
	public function send_invoice($transaction) {

		// Check if setting enabled
		if(!carbon_get_theme_option('kudos_email_receipt_enable')) {
			return;
		}

		$twig = new Kudos_Twig();
		$body = $twig->render('emails/invoice.html.twig', [
			'name' => !empty($transaction->name) ? $transaction->name : '',
			'date' => $transaction->time,
			'description' => __('One-off donation', 'kudos-donations'),
			'amount' => (!empty($transaction->currency) ? html_entity_decode(get_currency_symbol($transaction->currency)) : '') . number_format_i18n($transaction->value, 2),
			'receipt_id' => $transaction->order_id,
			'website_name' => get_bloginfo('name'),
		]);

		$headers = [
			"From: Kudos Donations <wordpress@iseard.media>"
		];

		self::send($transaction->email, __('Kudos Donation Receipt', 'kudos-donations'), $body, $headers);
	}

	/**
	 * Sends an invoice to the customer
	 *
	 * @since    1.1.0
	 * @param string $email
	 * @return bool
	 */
	public function send_test($email) {

		$twig = new Kudos_Twig();
		$body = $twig->render('emails/test.html.twig', ['website_name' => get_bloginfo('name')]);

		$headers = [
			"From: Kudos Donations <wordpress@iseard.media>"
		];

		return self::send($email, __('Test email', 'kudos-donations'), $body, $headers);
	}

	/**
	 * Email send function
	 *
	 * @since    1.1.0
	 * @param string $to
	 * @param string $subject
	 * @param string $body
	 * @param array $headers
	 * @return bool
	 */
	private function send($to, $subject, $body, $headers=[]) {

		// Use hook to modify existing config
		add_action('phpmailer_init', [$this, 'init']);
		$mail = wp_mail($to, $subject, $body, $headers);

		if($mail) {
			$this->logger->log(sprintf(__('Email with subject "%s" sent to "%s"', 'kudos_donations'), $subject, $to));
		} else {
			$this->logger->log(sprintf(__('Email with subject "%s" failed to be sent to "%s"', 'kudos_donations'), $subject, $to), 'WARNING');
		}

		// Remove action to prevent conflict
		remove_action('phpmailer_init', [$this, 'init']);

		return $mail;

	}

}