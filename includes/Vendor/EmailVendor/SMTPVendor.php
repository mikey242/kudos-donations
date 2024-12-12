<?php
/**
 * SMTP vendor.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Vendor\EmailVendor;

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Service\TwigService;
use IseardMedia\Kudos\Vendor\AbstractVendor;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use WP_Error;

class SMTPVendor extends AbstractVendor implements EmailVendorInterface {

	public const SETTING_CUSTOM_SMTP = '_kudos_custom_smtp';
	public const SETTING_SMTP_ENABLE = '_kudos_smtp_enable';
	public const SETTING_EMAIL_BCC = '_kudos_email_bcc';
	public const SETTING_SMTP_PASSWORD = '_kudos_smtp_password';
	public const SETTING_SMTP_PASSWORD_ENCRYPTED = '_kudos_smtp_password_encrypted';
	private const TEMPLATE_RECEIPT = 'emails/receipt.html.twig';
	private const TEMPLATE_MESSAGE = 'emails/message.html.twig';
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
		$this->twig = $twig;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name(): string {
		return 'SMTP';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action(): string {
		return 'kudos_mailer_send';
	}

	/**
	 * Sets html as the content type.
	 */
	public function set_html_mail_content_type(): string {
		return 'text/html';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->enable_custom_smtp = (bool) get_option( self::SETTING_SMTP_ENABLE, false );
		$this->bcc                = get_option( self::SETTING_EMAIL_BCC, '' );
		$this->custom_smtp_config = get_option( self::SETTING_CUSTOM_SMTP, [] );
	}

	/**
	 * Create the necessary hooks.
	 */
	private function add_hooks(): void {
		// Configure PHPMailer.
		add_action( 'phpmailer_init', [ $this, 'phpmailer_init' ] );

		// Add filters for encrypting passwords.
		add_filter( 'pre_update_option_' . self::SETTING_SMTP_PASSWORD, [ $this, 'encrypt_smtp_password' ] );

		// Add WordPress specific hooks.
		add_filter( 'wp_mail_from', [ $this, 'get_from_email' ], PHP_INT_MAX );
		add_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ], PHP_INT_MAX );
		add_filter( 'wp_mail_content_type', [ $this, 'set_html_mail_content_type' ] );
		add_action( 'wp_mail_failed', [ $this, 'handle_error' ] );
	}

	/**
	 * Removes hooks to return to normal settings after email sent.
	 */
	private function remove_hooks(): void {
		remove_action( 'phpmailer_init', [ $this, 'phpmailer_init' ] );
		remove_action( 'wp_mail_failed', [ $this, 'handle_error' ] );
		remove_filter( 'wp_mail_content_type', [ $this, 'set_html_mail_content_type' ] );
		remove_filter( 'wp_mail_from', [ $this, 'get_from_email' ], PHP_INT_MAX );
		remove_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ], PHP_INT_MAX );
	}

	/**
	 * Initializes the mailer by modifying default config if setting
	 * is enabled.
	 *
	 * @param PHPMailer $phpmailer PHPMailer instance.
	 *
	 * @throws Exception From PHPMailer.
	 *
	 */
	public function phpmailer_init( PHPMailer $phpmailer ): void {
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
		$this->logger->debug( 'PHPMailer initialized' );
	}

	/**
	 * Sends a message using the message template
	 *
	 * @param string $email Email address.
	 * @param string $header Message header.
	 * @param string $message Message body.
	 */
	public function send_message( string $email, string $header, string $message ): bool {
		$twig = $this->twig;
		$body = $twig->render(
			self::TEMPLATE_MESSAGE,
			[
				'header'  => $header,
				'message' => $message,
			]
		);

		return $this->send( $email, $header, $body );
	}

	/**
	 * Sends receipt to the donor.
	 *
	 * @param array $args The array of arguments used for sending the email.
	 */
	public function send_receipt( array $args ): bool {

		// Generate email body from args.
		$body = $this->twig->render( self::TEMPLATE_RECEIPT, $args );

		return $this->send(
			$args['email'],
			__( 'Donation Receipt', 'kudos-donations' ),
			$body,
			$args['attachments'] ?? null
		);
	}

	/**
	 * Email send function.
	 *
	 * @param string $to Recipient email address.
	 * @param string $subject Email subject line.
	 * @param string $body Body of email.
	 * @param array $attachment Attachment.
	 */
	private function send(
		string $to,
		string $subject,
		string $body,
		array $attachment = []
	): bool {
		do_action( 'kudos_mailer_send', $to, $subject, $body, $attachment );
		// Add required hooks.
		$this->add_hooks();

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
	 * Encrypts the SMTP password before storing it in the database.
	 *
	 * @param null|string $raw_password The raw password.
	 *
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
	public static function get_settings(): array {
		return [
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
