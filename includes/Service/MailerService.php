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

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\SubscriptionPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use WP_Error;
use WP_REST_Request;

class MailerService extends AbstractRegistrable implements HasSettingsInterface {

	public const SETTING_CUSTOM_SMTP             = '_kudos_custom_smtp';
	public const SETTING_SMTP_ENABLE             = '_kudos_smtp_enable';
	public const SETTING_EMAIL_BCC               = '_kudos_email_bcc';
	public const SETTING_EMAIL_RECEIPT_ENABLE    = '_kudos_email_receipt_enable';
	public const SETTING_SMTP_PASSWORD_ENCRYPTED = '_kudos_smtp_password_encrypted';
	private TwigService $twig;
	private EncryptionService $encryption;

	/**
	 * Mailer constructor.
	 *
	 * @param TwigService       $twig Twig service.
	 * @param EncryptionService $encryption Used for decrypting SMTP password.
	 */
	public function __construct( TwigService $twig, EncryptionService $encryption ) {
		$this->twig       = $twig;
		$this->encryption = $encryption;

		// Add filters for encrypting passwords.
		add_filter( 'pre_update_option_' . self::SETTING_CUSTOM_SMTP, [ $this, 'encrypt_smtp_password' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->logger->debug( 'Creating hooks' );
		add_action( 'phpmailer_init', [ $this, 'init' ] );
		add_action( 'wp_mail_failed', [ $this, 'handle_error' ] );
		if ( get_option( self::SETTING_CUSTOM_SMTP ) ) {
			add_filter( 'wp_mail_from', [ $this, 'get_from_email' ], PHP_INT_MAX );
			add_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ], PHP_INT_MAX );
		}
	}

	/**
	 * Returns the decrypted SMTP password.
	 */
	private function get_decrypted_smtp_password(): string {
		add_filter( 'option_' . self::SETTING_SMTP_PASSWORD_ENCRYPTED, [ $this->encryption, 'decrypt_password' ] );
		$password = get_option( self::SETTING_SMTP_PASSWORD_ENCRYPTED );
		remove_filter( 'option_' . self::SETTING_SMTP_PASSWORD_ENCRYPTED, [ $this->encryption, 'decrypt_password' ] );
		return $password;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_actions(): array {
		return [ 'kudos_mailer_send' ];
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
					'password'   => '',
				],
				'show_in_rest' => [
					'schema' => [
						'type'       => 'object',
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
							'password'   => [
								'type'         => FieldType::STRING,
								'show_in_rest' => false,
							],
						],
					],
				],
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
			Utils::get_logo(),
			'kudos-logo',
			'kudos-logo.png'
		);

		// Enable HTML email support.
		$phpmailer->isHTML();

		// Add BCC.
		$bcc = get_option( self::SETTING_EMAIL_BCC );
		if ( is_email( $bcc ) ) {
			$phpmailer->addBCC( $bcc );
		}
		// Add custom config if enabled.
		if ( get_option( self::SETTING_SMTP_ENABLE ) ) {
			$custom_config = get_option( self::SETTING_CUSTOM_SMTP );
			$this->logger->debug( 'Using custom SMTP config' );

			// Get password.
			$password = $this->get_decrypted_smtp_password();

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
			'name'         => $donor->{DonorPostType::META_FIELD_NAME} ?? '',
			'date'         => $transaction->post_date,
			'description'  => $transaction->post_title,
			'amount'       => ( ! empty( $transaction->{TransactionPostType::META_FIELD_CURRENCY} ) ? html_entity_decode(
				Utils::get_currencies()[ $transaction->{TransactionPostType::META_FIELD_CURRENCY} ]
			) : '' ) . number_format_i18n(
				$transaction->{TransactionPostType::META_FIELD_VALUE},
				2
			),
			'receipt_id'   => TransactionPostType::get_formatted_id( $transaction_id ),
			'website_name' => get_bloginfo( 'name' ),
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
						'token'        => Utils::generate_token( $subscription->ID ),
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
		if ( get_option( self::SETTING_CUSTOM_SMTP ) ) {
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
		return filter_var( get_option( self::SETTING_CUSTOM_SMTP )['from_email'], FILTER_VALIDATE_EMAIL );
	}

	/**
	 * Returns a filtered name.
	 */
	public function get_from_name(): string {
		return get_option( self::SETTING_CUSTOM_SMTP )['from_name'];
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
	 * Encrypts the SMTP password before storing it in the database.
	 *
	 * @throws \Exception Thrown when there is a problem encrypting the password.
	 *
	 * @param array $setting The SMTP settings array.
	 * @return array The modified settings array with the masked password.
	 */
	public function encrypt_smtp_password( array $setting ): array {
		$raw_password = $setting['password'] ?? null;

		if ( $raw_password ) {
			// Bail if this is only asterisks.
			$num_asterisks = substr_count( $raw_password, '*' );
			$count         = \strlen( $raw_password );
			if ( $num_asterisks === $count ) {
				return $setting;
			}
			// Encrypt the password.
			$encrypted_password = $this->encryption->encrypt_password( $raw_password );

			// Update the encrypted password in the database.
			update_option( self::SETTING_SMTP_PASSWORD_ENCRYPTED, $encrypted_password );

			// Replace the password with asterisks in the settings array.
			$setting['password'] = str_repeat( '*', $count );
		} else {
			// If no password is provided, retain the existing masked password or set it to null.
			$setting['password'] = get_option( self::SETTING_CUSTOM_SMTP )['password'] ?? null;
		}
		return $setting;
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
