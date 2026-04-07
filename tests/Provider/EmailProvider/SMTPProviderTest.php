<?php
/**
 * SMTP provider integration test.
 *
 * Requires a running Mailpit instance. Set MAILPIT_SMTP_PORT and
 * MAILPIT_HTTP_PORT environment variables before running.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Tests\Provider\EmailProvider;

use IseardMedia\Kudos\Provider\EmailProvider\SMTPProvider;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Provider\EmailProvider\SMTPProvider
 */
class SMTPProviderTest extends BaseTestCase {

	private SMTPProvider $provider;
	private string $mailpit_api_url;

	public function set_up(): void {
		parent::set_up();

		$smtp_port = getenv( 'MAILPIT_SMTP_PORT' );
		$http_port = getenv( 'MAILPIT_HTTP_PORT' );

		if ( ! $smtp_port || ! $http_port ) {
			$this->markTestSkipped( 'Mailpit not available (MAILPIT_SMTP_PORT and MAILPIT_HTTP_PORT must be set).' );
		}

		$this->mailpit_api_url = "http://127.0.0.1:{$http_port}/api/v1";

		update_option(
			SMTPProvider::SETTING_CUSTOM_SMTP,
			[
				'host'       => '127.0.0.1',
				'port'       => (int) $smtp_port,
				'encryption' => 'none',
				'username'   => 'test',
				'from_email' => 'noreply@example.com',
				'from_name'  => 'Kudos Test',
			]
		);
		update_option( SMTPProvider::SETTING_SMTP_ENABLE, true );

		/** @var SMTPProvider $provider */
		$provider       = $this->get_from_container( SMTPProvider::class );
		$this->provider = $provider;
		$this->provider->init();

		$this->clear_mailpit_messages();
	}

	public function tear_down(): void {
		$this->clear_mailpit_messages();
		parent::tear_down();
	}

	/**
	 * Test that send_message delivers the email to the SMTP server.
	 */
	public function test_send_message_delivers_email(): void {
		$result = $this->provider->send_message(
			'recipient@example.com',
			'Test Subject',
			'Hello from the Kudos SMTP test!'
		);

		$this->assertTrue( $result, 'send_message() should return true on success.' );

		$messages = $this->get_mailpit_messages();
		$this->assertCount( 1, $messages, 'Exactly one message should be in Mailpit.' );
		$this->assertSame( 'Test Subject', $messages[0]['Subject'] );
		$this->assertSame( 'recipient@example.com', $messages[0]['To'][0]['Address'] );
		$this->assertSame( 'noreply@example.com', $messages[0]['From']['Address'] );
	}

	/**
	 * Test that the From name is applied correctly.
	 */
	public function test_send_message_sets_from_name(): void {
		$this->provider->send_message( 'recipient@example.com', 'From Name Test', 'Body.' );

		$messages = $this->get_mailpit_messages();
		$this->assertNotEmpty( $messages );
		$this->assertSame( 'Kudos Test', $messages[0]['From']['Name'] );
	}

	/**
	 * Fetches all messages from the Mailpit API.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function get_mailpit_messages(): array {
		$response = wp_remote_get( $this->mailpit_api_url . '/messages' );
		$body     = json_decode( wp_remote_retrieve_body( $response ), true );
		return $body['messages'] ?? [];
	}

	/**
	 * Deletes all messages from Mailpit.
	 */
	private function clear_mailpit_messages(): void {
		wp_remote_request(
			$this->mailpit_api_url . '/messages',
			[ 'method' => 'DELETE' ]
		);
	}
}