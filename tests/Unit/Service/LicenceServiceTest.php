<?php
/**
 * LicenceService tests.
 */

namespace IseardMedia\Kudos\Tests\Service;

use IseardMedia\Kudos\Service\LicenceService;
use IseardMedia\Kudos\Tests\BaseTestCase;

/**
 * @covers \IseardMedia\Kudos\Service\LicenceService
 */
class LicenceServiceTest extends BaseTestCase {

	/**
	 * Original $_SERVER['SERVER_NAME'], restored after each test.
	 *
	 * @var string|null
	 */
	private ?string $server_name;

	/**
	 * Requests sent to the licence server during a test.
	 *
	 * @var array<int, array{url: string, args: array}>
	 */
	private array $requests = [];

	public function set_up(): void {
		parent::set_up();
		$this->server_name = $_SERVER['SERVER_NAME'] ?? null;
		add_filter( 'pre_http_request', [ $this, 'capture_request' ], 10, 3 );
	}

	public function tear_down(): void {
		remove_filter( 'pre_http_request', [ $this, 'capture_request' ] );
		if ( null === $this->server_name ) {
			unset( $_SERVER['SERVER_NAME'] );
		} else {
			$_SERVER['SERVER_NAME'] = $this->server_name;
		}
		parent::tear_down();
	}

	/**
	 * Records the request and answers it as a successful activation.
	 *
	 * @param false|array $response Short-circuit response.
	 * @param array       $args     Request arguments.
	 * @param string      $url      Request URL.
	 */
	public function capture_request( $response, array $args, string $url ): array {
		$this->requests[] = compact( 'url', 'args' );

		return [
			'headers'  => [],
			'body'     => wp_json_encode( [ 'success' => true ] ),
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
			'cookies'  => [],
		];
	}

	public function test_site_domain_is_home_url_host(): void {
		update_option( 'home', 'https://www.example.org/blog' );

		$this->assertSame( 'www.example.org', LicenceService::get_site_domain() );
	}

	/**
	 * SERVER_NAME is empty under WP-CLI and cron, and can be "_" or an internal name behind
	 * Nginx or a proxy: it must not affect the domain sent to the licence server.
	 */
	public function test_activation_sends_home_url_host_not_server_name(): void {
		update_option( 'home', 'https://shop.example.org' );
		$_SERVER['SERVER_NAME'] = '_';

		( new LicenceService() )->handle_key_update( 'ABCD-EF01-2345-6789', '' );

		$this->assertCount( 1, $this->requests );
		$this->assertStringEndsWith( '/licences/activate', $this->requests[0]['url'] );
		$body = json_decode( $this->requests[0]['args']['body'], true );
		$this->assertSame( 'shop.example.org', $body['domain'] );
	}
}
