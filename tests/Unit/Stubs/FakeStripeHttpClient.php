<?php

namespace IseardMedia\Kudos\Tests\Stubs;

use IseardMedia\Kudos\ThirdParty\Stripe\HttpClient\ClientInterface;

/**
 * Fake Stripe HTTP client for unit tests.
 *
 * Intercepts HTTP calls made by the Stripe SDK via ApiRequestor::setHttpClient()
 * and returns preset JSON responses without making real network requests.
 *
 * Usage:
 *   $client = new FakeStripeHttpClient();
 *   $client->set_response('checkout/sessions', ['id' => 'cs_test', 'url' => '...']);
 *   ApiRequestor::setHttpClient($client);
 */
class FakeStripeHttpClient implements ClientInterface {

	/** @var array<string, array{0: string, 1: int, 2: array}> Keyed by URL fragment. */
	private array $responses = [];

	/** @var list<array{method: string, absUrl: string, params: mixed}> */
	private array $requests = [];

	/**
	 * Register a response for any request whose URL contains $url_fragment.
	 *
	 * @param string $url_fragment Substring to match against the request URL.
	 * @param array  $body        Response body (will be JSON-encoded).
	 * @param int    $code        HTTP status code (default 200).
	 */
	public function set_response( string $url_fragment, array $body, int $code = 200 ): void {
		$this->responses[ $url_fragment ] = [ json_encode( $body ), $code, [] ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function request( $method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null ): array {
		$this->requests[] = [
			'method'  => $method,
			'absUrl'  => $absUrl,
			'params'  => $params,
		];

		foreach ( $this->responses as $fragment => $response ) {
			if ( str_contains( $absUrl, $fragment ) ) {
				return $response;
			}
		}

		return [
			json_encode(
				[
					'error' => [
						'type'    => 'api_error',
						'message' => 'No fake response configured for ' . $absUrl,
					],
				]
			),
			500,
			[],
		];
	}

	/**
	 * Returns all recorded requests, oldest first.
	 *
	 * @return list<array{method: string, absUrl: string, params: mixed}>
	 */
	public function get_requests(): array {
		return $this->requests;
	}

	/**
	 * Returns the most recently recorded request, or null if none.
	 *
	 * @return array{method: string, absUrl: string, params: mixed}|null
	 */
	public function get_last_request(): ?array {
		return $this->requests ? end( $this->requests ) : null;
	}
}