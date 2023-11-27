<?php
/**
 * Mailer service.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Helper\Utils;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;
use WP_Error;
use WP_REST_Request;

class MailerService {

	private TwigService $twig;
	private LoggerInterface $logger;
	private SettingsService $settings;

	/**
	 * Mailer constructor.
	 *
	 * @param TwigService     $twig Twig service.
	 * @param LoggerInterface $logger Logger instance.
	 * @param SettingsService $settings Settings service.
	 */
	public function __construct( TwigService $twig, LoggerInterface $logger, SettingsService $settings ) {
		$this->settings = $settings;
		$this->logger   = $logger;
		$this->twig     = $twig;
	}

	/**
	 * Bse64 encoded logo.
	 */
	private function get_logo(): string {
		return 'iVBORw0KGgoAAAANSUhEUgAAACgAAAAgCAYAAABgrToAAAAFVGlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS41LjAiPgogPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICAgeG1sbnM6ZXhpZj0iaHR0cDovL25zLmFkb2JlLmNvbS9leGlmLzEuMC8iCiAgICB4bWxuczp0aWZmPSJodHRwOi8vbnMuYWRvYmUuY29tL3RpZmYvMS4wLyIKICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIKICAgIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIKICAgIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIgogICAgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIKICAgZXhpZjpQaXhlbFhEaW1lbnNpb249IjQwIgogICBleGlmOlBpeGVsWURpbWVuc2lvbj0iMzIiCiAgIGV4aWY6Q29sb3JTcGFjZT0iMSIKICAgdGlmZjpJbWFnZVdpZHRoPSI0MCIKICAgdGlmZjpJbWFnZUxlbmd0aD0iMzIiCiAgIHRpZmY6UmVzb2x1dGlvblVuaXQ9IjIiCiAgIHRpZmY6WFJlc29sdXRpb249IjcyLzEiCiAgIHRpZmY6WVJlc29sdXRpb249IjcyLzEiCiAgIHBob3Rvc2hvcDpDb2xvck1vZGU9IjMiCiAgIHBob3Rvc2hvcDpJQ0NQcm9maWxl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';
	}

	/**
	 * Initializes the mailer by modifying default config if setting
	 * is enabled.
	 *
	 * @throws Exception From PHPMailer.
	 *
	 * @param PHPMailer $phpmailer PHPMailer instance.
	 */
	public function init( PHPMailer $phpmailer ): void {
		$this->logger->debug( 'Mailer: PHPMailer initialized' );
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$phpmailer->SMTPDebug = 0;

		// Set higher timeout.
		$phpmailer->Timeout = 10;

		// Add logo as attachment.
		$phpmailer->addStringEmbeddedImage(
			// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			base64_decode( $this->get_logo() ),
			'kudos-logo',
			'kudos-logo.png'
		);

		// Enable HTML email support.
		$phpmailer->isHTML();

		// Add BCC.
		$bcc = $this->settings->get_setting( SettingsService::SETTING_NAME_EMAIL_BCC );
		if ( is_email( $bcc ) ) {
			$phpmailer->addBCC( $bcc );
		}
		// Add custom config if enabled.
		if ( $this->settings->get_setting( SettingsService::SETTING_NAME_SMTP_ENABLE ) ) {
			$custom_config = $this->settings->get_setting( SettingsService::SETTING_NAME_CUSTOM_SMTP );
			$this->logger->debug( 'Mailer: Using custom SMTP config' );
			$phpmailer->isSMTP();
			$phpmailer->Host        = $custom_config['host'];
			$phpmailer->SMTPAutoTLS = true;
			$phpmailer->SMTPAuth    = true;
			if ( 'none' !== $custom_config['encryption'] ) {
				$phpmailer->SMTPSecure = $custom_config['encryption'];
			}
			$phpmailer->Username = $custom_config['username'];
			$phpmailer->Password = $custom_config['password'];
			$phpmailer->Port     = $custom_config['port'];
			$phpmailer->From     = $custom_config['from_email'];
			$phpmailer->FromName = $custom_config['from_name'];
		}
		// phpcs:enable
	}

	/**
	 * Sends receipt to the donor.
	 *
	 * @param int $donor_id Donor id.
	 * @param int $transaction_id Transaction id.
	 */
	public function send_receipt( int $donor_id, int $transaction_id ): bool {
		// Check if setting enabled.
		if ( ! $this->settings->get_setting( SettingsService::SETTING_NAME_EMAIL_RECEIPT_ENABLE ) ) {
			return false;
		}

		// Assign attachment.
		$attachments = apply_filters( 'kudos_receipt_attachment', [], $transaction_id );

		// Get donor details.
		$donor_meta       = get_post_meta( $donor_id );
		$transaction_meta = get_post_meta( $transaction_id );

		// Create array of variables for use in twig template.
		$render_array = [
			'name'         => $donor_meta['name'][0] ?? '',
			'date'         => get_post( $transaction_id )->post_date,
			'description'  => $transaction_meta['description'][0] ?? '',
			'amount'       => ( ! empty( $transaction_meta['currency'][0] ) ? html_entity_decode(
				Utils::get_currency_symbol( $transaction_meta['currency'][0] )
			) : '' ) . number_format_i18n(
				$transaction_meta['value'][0],
				2
			),
			'receipt_id'   => TransactionPostType::get_formatted_id( $transaction_id ),
			'website_name' => get_bloginfo( 'name' ),
		];

		$twig = $this->twig;
		$body = $twig->render( 'emails/receipt.html.twig', $render_array );

		$this->logger->debug(
			'Creating receipt email.',
			array_merge(
				[
					'email' => $donor_meta['email'],
					$render_array,
				]
			)
		);

		return $this->send(
			$donor_meta['email'][0],
			__( 'Donation Receipt', 'kudos-donations' ),
			$body,
			$attachments
		);
	}

	/**
	 * Email send function.
	 *
	 * @param string $to Recipient email address.
	 * @param string $subject Email subject line.
	 * @param string $body Body of email.
	 * @param array  $attachment Attachment.
	 */
	public function send(
		string $to,
		string $subject,
		string $body,
		array $attachment = []
	): bool {
		// Use hook to modify existing config.
		$this->create_hooks();

		$mail = wp_mail( $to, $subject, $body, '', $attachment );

		if ( $mail ) {
			$this->logger->debug(
				'Email sent successfully.',
				[
					'to'      => $to,
					'subject' => $subject,
				]
			);
		}

		// Remove hooks once send complete.
		$this->remove_hooks();

		return $mail;
	}

	/**
	 * Creates the hooks for changing phpmailer settings.
	 */
	private function create_hooks(): void {
		add_action( 'phpmailer_init', [ $this, 'init' ] );
		add_action( 'wp_mail_failed', [ $this, 'handle_error' ] );
		if ( $this->settings->get_setting( SettingsService::SETTING_NAME_CUSTOM_SMTP ) ) {
			add_filter( 'wp_mail_from', [ $this, 'get_from_email' ], PHP_INT_MAX );
			add_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ], PHP_INT_MAX );
		}
	}

	/**
	 * Removes hooks to return to normal settings after email sent.
	 */
	private function remove_hooks(): void {
		remove_action( 'phpmailer_init', [ $this, 'init' ] );
		remove_action( 'wp_mail_failed', [ $this, 'handle_error' ] );
		if ( $this->settings->get_setting( SettingsService::SETTING_NAME_CUSTOM_SMTP ) ) {
			remove_filter( 'wp_mail_from', [ $this, 'get_from_email' ], PHP_INT_MAX );
			remove_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ], PHP_INT_MAX );
		}
	}

	/**
	 * Returns a filtered email.
	 */
	public function get_from_email(): string {
		return filter_var( $this->settings->get_setting( SettingsService::SETTING_NAME_CUSTOM_SMTP )['from_email'], FILTER_VALIDATE_EMAIL );
	}

	/**
	 * Returns a filtered name.
	 */
	public function get_from_name(): string {
		return $this->settings->get_setting( SettingsService::SETTING_NAME_CUSTOM_SMTP )['from_name'];
	}

	/**
	 * Sends a test email using send_message.
	 *
	 * @param WP_REST_Request $request Request array.
	 */
	public function send_test( WP_REST_Request $request ): bool {
		if ( empty( $request['email'] ) ) {
			wp_send_json_error( __( 'Please provide an email address.', 'kudos-donations' ) );
		}

		$email   = sanitize_email( $request['email'] );
		$header  = __( 'It worked!', 'kudos-donations' );
		$message = __( 'Looks like your email settings are set up correctly :-)', 'kudos-donations' );

		$result = $this->send_message( $email, $header, $message );

		if ( $result ) {
			/* translators: %s: API mode */
			wp_send_json_success( sprintf( __( 'Email sent to %s.', 'kudos-donations' ), $email ) );
		} else {
			/* translators: %s: API mode */
			wp_send_json_error(
				__(
					'Error sending email, please check the settings and try again.',
					'kudos-donations'
				)
			);
		}

		return $result;
	}

	/**
	 * Sends a message using the message template
	 *
	 * @param string $email Email address.
	 * @param string $header Email headers.
	 * @param string $message Email body.
	 */
	public function send_message( string $email, string $header, string $message ): bool {
		$twig = $this->twig;
		$body = $twig->render(
			'emails/message.html.twig',
			[
				'header'       => $header,
				'message'      => $message,
				'website_name' => get_bloginfo( 'name' ),
			]
		);

		return $this->send( $email, $header, $body );
	}

	/**
	 * Logs the supplied WP_Error object.
	 *
	 * @param WP_Error $error WP_Error object.
	 */
	public function handle_error( WP_Error $error ): void {
		$this->logger->error( 'Error sending email.', [ $error->get_error_messages() ] );
		wp_send_json_error( $error->get_error_messages() );
	}
}
