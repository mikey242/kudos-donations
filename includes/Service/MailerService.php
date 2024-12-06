<?php
/**
 * Mailer service.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\EncryptionAwareInterface;
use IseardMedia\Kudos\Container\EncryptionAwareTrait;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\SubscriptionPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use WP_Error;

class MailerService extends AbstractRegistrable implements HasSettingsInterface, EncryptionAwareInterface {

	use EncryptionAwareTrait;

	public const SETTING_CUSTOM_SMTP             = '_kudos_custom_smtp';
	public const SETTING_SMTP_ENABLE             = '_kudos_smtp_enable';
	public const SETTING_EMAIL_BCC               = '_kudos_email_bcc';
	public const SETTING_EMAIL_RECEIPT_ENABLE    = '_kudos_email_receipt_enable';
	public const SETTING_SMTP_PASSWORD           = '_kudos_smtp_password';
	public const SETTING_SMTP_PASSWORD_ENCRYPTED = '_kudos_smtp_password_encrypted';
	private TwigService $twig;
	private bool $enable_custom_smtp;
	private ?string $bcc;
	private array $custom_smtp_config;

	/**
	 * Mailer constructor.
	 *
	 * @param TwigService $twig Twig service.
	 */
	public function __construct( TwigService $twig ) {
		$this->twig               = $twig;
		$this->enable_custom_smtp = (bool) get_option( self::SETTING_SMTP_ENABLE, false );
		$this->bcc                = get_option( self::SETTING_EMAIL_BCC, '' );
		$this->custom_smtp_config = get_option( self::SETTING_CUSTOM_SMTP, [] );

		// Add filters for encrypting passwords.
		add_filter( 'pre_update_option_' . self::SETTING_SMTP_PASSWORD, [ $this, 'encrypt_smtp_password' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->logger->debug( 'Creating hooks' );
		add_action( 'phpmailer_init', [ $this, 'init' ] );
		add_action( 'wp_mail_failed', [ $this, 'handle_error' ] );
		if ( $this->custom_smtp_config ) {
			add_filter( 'wp_mail_from', [ $this, 'get_from_email' ], PHP_INT_MAX );
			add_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ], PHP_INT_MAX );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action(): string {
		return 'kudos_mailer_send';
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
		$this->logger->debug( 'PHPMailer initialized' );
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$phpmailer->SMTPDebug = 0;

		// Set higher timeout.
		$phpmailer->Timeout = 10;

		// Add logo as attachment.
		$phpmailer->addStringEmbeddedImage(
			Utils::get_company_logo_png(),
			'logo',
			'logo.png'
		);

		// Enable HTML email support.
		$phpmailer->isHTML();

		// Add BCC.
		$bcc = $this->bcc;
		if ( is_email( $bcc ) ) {
			$phpmailer->addBCC( $bcc );
		}
		// Add custom config if enabled.
		if ( $this->enable_custom_smtp ) {
			$custom_config = $this->custom_smtp_config;
			$this->logger->debug( 'Using custom SMTP config' );

			// Get password.
			$password = $this->get_decrypted_key( self::SETTING_SMTP_PASSWORD_ENCRYPTED );

			$phpmailer->isSMTP();
			$phpmailer->Host        = $custom_config['host'];
			$phpmailer->SMTPAutoTLS = true;
			$phpmailer->SMTPAuth    = true;
			if ( 'none' !== $custom_config['encryption'] ) {
				$phpmailer->SMTPSecure = $custom_config['encryption'];
			}
			$phpmailer->Username = $custom_config['username'];
			$phpmailer->Password = $password;
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
		if ( ! get_option( self::SETTING_EMAIL_RECEIPT_ENABLE ) ) {
			return false;
		}

		// Assign attachment.
		$attachments = apply_filters( 'kudos_receipt_attachment', [], $transaction_id );

		// Get posts.
		$donor       = get_post( $donor_id );
		$transaction = get_post( $transaction_id );

		// Create array of variables for use in twig template.
		$render_array = [
			'name'        => $donor->{DonorPostType::META_FIELD_NAME} ?? '',
			'date'        => $transaction->post_date,
			'description' => $transaction->post_title,
			'amount'      => ( ! empty( $transaction->{TransactionPostType::META_FIELD_CURRENCY} ) ? html_entity_decode(
				Utils::get_currencies()[ $transaction->{TransactionPostType::META_FIELD_CURRENCY} ]
			) : '' ) . number_format_i18n(
				$transaction->{TransactionPostType::META_FIELD_VALUE},
				2
			),
			'receipt_id'  => Utils::get_formatted_id( $transaction_id ),
		];

		// Add a cancel button if this is the receipt for a subscription payment.
		try {
			if ( 'oneoff' !== $transaction->{TransactionPostType::META_FIELD_SEQUENCE_TYPE} ) {
				$subscription = SubscriptionPostType::get_post(
					[
						SubscriptionPostType::META_FIELD_VENDOR_SUBSCRIPTION_ID => $transaction->{TransactionPostType::META_FIELD_VENDOR_SUBSCRIPTION_ID},
					]
				);
				$this->logger->debug( 'Detected recurring payment. Adding cancel button.', [ SubscriptionPostType::META_FIELD_TRANSACTION_ID => $transaction_id ] );
				$render_array['cancel_url'] = add_query_arg(
					[
						'kudos_action' => 'cancel_subscription',
						'token'        => EncryptionService::generate_token( $subscription->ID ),
						'id'           => $subscription->ID,
					],
					apply_filters( 'kudos_cancel_subscription_url', get_home_url() )
				);
			}
		} catch ( \Exception $e ) {
			$this->logger->error( 'Error adding cancel button: ' . $e->getMessage() );
		}

		$body = $this->twig->render( 'emails/receipt.html.twig', $render_array );

		$this->logger->debug(
			'Creating receipt email.',
			array_merge(
				[
					'email' => $donor->{DonorPostType::META_FIELD_EMAIL},
					$render_array,
				]
			)
		);

		return $this->send(
			$donor->{DonorPostType::META_FIELD_EMAIL},
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
		do_action( 'kudos_mailer_send', $to, $subject, $body, $attachment );

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
	 * Removes hooks to return to normal settings after email sent.
	 */
	private function remove_hooks(): void {
		$this->logger->debug( 'Removing hooks' );
		remove_action( 'phpmailer_init', [ $this, 'init' ] );
		remove_action( 'wp_mail_failed', [ $this, 'handle_error' ] );
		if ( $this->custom_smtp_config ) {
			remove_filter( 'wp_mail_from', [ $this, 'get_from_email' ], PHP_INT_MAX );
			remove_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ], PHP_INT_MAX );
		}
	}

	/**
	 * Returns a filtered email.
	 *
	 * @return string|false
	 */
	public function get_from_email() {
		return filter_var( $this->custom_smtp_config['from_email'], FILTER_VALIDATE_EMAIL );
	}

	/**
	 * Returns a filtered name.
	 */
	public function get_from_name(): string {
		return $this->custom_smtp_config['from_name'];
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
				'header'  => $header,
				'message' => $message,
			]
		);

		return $this->send( $email, $header, $body );
	}

	/**
	 * Encrypts the SMTP password before storing it in the database.
	 *
	 * @param null|string $raw_password The raw password.
	 * @return string The masked password.
	 */
	public function encrypt_smtp_password( ?string $raw_password ): string {
		return $this->save_encrypted_key( $raw_password, self::SETTING_SMTP_PASSWORD_ENCRYPTED );
	}

	/**
	 * Logs the supplied WP_Error object.
	 *
	 * @param WP_Error $error WP_Error object.
	 */
	public function handle_error( WP_Error $error ): void {
		$this->logger->error( 'Error sending email.', [ $error->get_error_messages() ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_settings(): array {
		return [
			self::SETTING_EMAIL_RECEIPT_ENABLE    => [
				'type'              => FieldType::BOOLEAN,
				'show_in_rest'      => true,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
			self::SETTING_EMAIL_BCC               => [
				'type'              => FieldType::STRING,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_email',
			],
			self::SETTING_CUSTOM_SMTP             => [
				'type'         => 'object',
				'default'      => [
					'from_email' => '',
					'from_name'  => get_bloginfo( 'name' ),
					'host'       => '',
					'port'       => '',
					'encryption' => 'tls',
					'autotls'    => false,
					'username'   => '',
				],
				'show_in_rest' => [
					'schema' => [
						'type'       => FieldType::OBJECT,
						'properties' => [
							'from_email' => [
								'type' => FieldType::STRING,
							],
							'from_name'  => [
								'type' => FieldType::STRING,
							],
							'host'       => [
								'type' => FieldType::STRING,
							],
							'port'       => [
								'type' => FieldType::INTEGER,
							],
							'encryption' => [
								'type' => FieldType::STRING,
							],
							'autotls'    => [
								'type' => FieldType::BOOLEAN,
							],
							'username'   => [
								'type' => FieldType::STRING,
							],
						],
					],
				],
			],
			self::SETTING_SMTP_PASSWORD           => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
			],
			self::SETTING_SMTP_PASSWORD_ENCRYPTED => [
				'type'         => FieldType::STRING,
				'show_in_rest' => false,
			],
			self::SETTING_SMTP_ENABLE             => [
				'type'              => FieldType::BOOLEAN,
				'show_in_rest'      => true,
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			],
		];
	}
}
