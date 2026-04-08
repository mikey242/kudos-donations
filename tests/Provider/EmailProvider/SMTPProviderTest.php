<?php
/**
 * SMTP provider tests.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Tests\Provider\EmailProvider;

use IseardMedia\Kudos\Provider\EmailProvider\SMTPProvider;
use IseardMedia\Kudos\Tests\BaseTestCase;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * @covers \IseardMedia\Kudos\Provider\EmailProvider\SMTPProvider
 */
class SMTPProviderTest extends BaseTestCase {

	private SMTPProvider $provider;

	private array $smtp_config = [
		'host'       => 'smtp.example.com',
		'port'       => 587,
		'encryption' => 'tls',
		'username'   => 'user@example.com',
		'from_email' => 'noreply@example.com',
		'from_name'  => 'Kudos Test',
	];

	public function set_up(): void {
		parent::set_up();
		reset_phpmailer_instance();
		add_filter( 'wp_mail_from', [ $this, 'get_test_from_email' ] );

		/** @var SMTPProvider $provider */
		$this->provider = $this->get_from_container( SMTPProvider::class );
	}

	public function tear_down(): void {
		remove_filter( 'wp_mail_from', [ $this, 'get_test_from_email' ] );
		parent::tear_down();
	}

	public function get_test_from_email(): string {
		return 'noreply@example.com';
	}

	/**
	 * Test that phpmailer_init sets SMTP mode and all expected properties when custom SMTP is enabled.
	 */
	public function test_phpmailer_init_configures_smtp_when_enabled(): void {
		update_option( SMTPProvider::SETTING_SMTP_ENABLE, true );
		update_option( SMTPProvider::SETTING_CUSTOM_SMTP, $this->smtp_config );
		$this->provider->init();

		$phpmailer = new PHPMailer();
		$this->provider->phpmailer_init( $phpmailer );

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->assertSame( 'smtp', $phpmailer->Mailer );
		$this->assertSame( 'smtp.example.com', $phpmailer->Host );
		$this->assertSame( 587, $phpmailer->Port );
		$this->assertTrue( $phpmailer->SMTPAuth );
		$this->assertSame( 'user@example.com', $phpmailer->Username );
		$this->assertSame( 'noreply@example.com', $phpmailer->From );
		$this->assertSame( 'Kudos Test', $phpmailer->FromName );
		// phpcs:enable
	}

	/**
	 * Test that encryption 'none' leaves SMTPSecure unset.
	 */
	public function test_phpmailer_init_leaves_smtp_secure_empty_when_encryption_is_none(): void {
		$config = array_merge( $this->smtp_config, [ 'encryption' => 'none' ] );
		update_option( SMTPProvider::SETTING_SMTP_ENABLE, true );
		update_option( SMTPProvider::SETTING_CUSTOM_SMTP, $config );
		$this->provider->init();

		$phpmailer = new PHPMailer();
		$this->provider->phpmailer_init( $phpmailer );

		$this->assertSame( '', $phpmailer->SMTPSecure ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Test that a non-'none' encryption value is applied to SMTPSecure.
	 */
	public function test_phpmailer_init_sets_smtp_secure_when_encryption_provided(): void {
		update_option( SMTPProvider::SETTING_SMTP_ENABLE, true );
		update_option( SMTPProvider::SETTING_CUSTOM_SMTP, $this->smtp_config );
		$this->provider->init();

		$phpmailer = new PHPMailer();
		$this->provider->phpmailer_init( $phpmailer );

		$this->assertSame( 'tls', $phpmailer->SMTPSecure ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Test that phpmailer_init does not switch to SMTP mode when custom SMTP is disabled.
	 */
	public function test_phpmailer_init_does_not_configure_smtp_when_disabled(): void {
		update_option( SMTPProvider::SETTING_SMTP_ENABLE, false );
		$this->provider->init();

		$phpmailer = new PHPMailer();
		$this->provider->phpmailer_init( $phpmailer );

		$this->assertNotSame( 'smtp', $phpmailer->Mailer ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Test that a valid BCC address is added to the mailer.
	 */
	public function test_phpmailer_init_adds_bcc_when_valid_email_set(): void {
		update_option( SMTPProvider::SETTING_SMTP_ENABLE, false );
		update_option( SMTPProvider::SETTING_EMAIL_BCC, 'bcc@example.com' );
		$this->provider->init();

		$phpmailer = new PHPMailer();
		$this->provider->phpmailer_init( $phpmailer );

		$bcc_addresses = array_column( $phpmailer->getBccAddresses(), 0 );
		$this->assertContains( 'bcc@example.com', $bcc_addresses );
	}

	/**
	 * Test that an invalid BCC address is not added.
	 */
	public function test_phpmailer_init_ignores_invalid_bcc(): void {
		update_option( SMTPProvider::SETTING_SMTP_ENABLE, false );
		update_option( SMTPProvider::SETTING_EMAIL_BCC, 'not-an-email' );
		$this->provider->init();

		$phpmailer = new PHPMailer();
		$this->provider->phpmailer_init( $phpmailer );

		$this->assertEmpty( $phpmailer->getBccAddresses() );
	}

	/**
	 * Test that send_message passes the correct recipient and subject to wp_mail.
	 */
	public function test_send_message_sends_to_correct_recipient(): void {
		update_option( SMTPProvider::SETTING_SMTP_ENABLE, false );
		$this->provider->init();

		$this->provider->send_message( 'recipient@example.com', 'Test Subject', 'Hello!' );

		$mailer = tests_retrieve_phpmailer_instance();
		$this->assertSame( 'recipient@example.com', $mailer->mock_sent[0]['to'][0][0] );
		$this->assertSame( 'Test Subject', $mailer->mock_sent[0]['subject'] );
	}
}