<?php
namespace IseardMedia\Kudos\Tests\Controller\Rest;

use IseardMedia\Kudos\Controller\Rest\Payment;
use IseardMedia\Kudos\Tests\BaseTestCase;
use WP_REST_Request;

/**
 * Covers the controller's vendor-aware webhook dispatch: requests carrying a vendor slug route to
 * that provider, and slug-less (legacy) requests fall back to Mollie — regardless of which provider
 * is currently active.
 *
 * @covers \IseardMedia\Kudos\Controller\Rest\Payment::handle_webhook
 */
class PaymentWebhookDispatchTest extends BaseTestCase {

	private Payment $controller;

	protected function setUp(): void {
		parent::setUp();
		$this->controller = $this->get_from_container( Payment::class );
	}

	/**
	 * A webhook URL without a vendor slug predates per-vendor URLs and must route to Mollie.
	 * Mollie's handler returns a response containing a "_links" key, which Stripe's does not.
	 */
	public function test_slugless_webhook_routes_to_mollie(): void {
		$request  = new WP_REST_Request( 'POST', '/kudos/v1/payment/webhook' );
		$response = $this->controller->handle_webhook( $request );

		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( $response->data['success'] );
		$this->assertArrayHasKey( '_links', $response->data );
	}

	/**
	 * An explicit "mollie" slug resolves to the Mollie provider — the non-fallback branch,
	 * where the slug is present in the URL rather than defaulted.
	 */
	public function test_explicit_mollie_slug_routes_to_mollie(): void {
		$request = new WP_REST_Request( 'POST', '/kudos/v1/payment/webhook/mollie' );
		$request->set_param( 'vendor', 'mollie' );
		$response = $this->controller->handle_webhook( $request );

		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( $response->data['success'] );
		$this->assertArrayHasKey( '_links', $response->data );
	}

	/**
	 * An explicit vendor slug routes to that provider even when it is not the active one.
	 * Stripe has no secret configured here, so its handler returns 400 — proving the request
	 * reached Stripe rather than the default Mollie provider (which would have returned 200).
	 */
	public function test_vendor_slug_routes_to_named_provider(): void {
		$request = new WP_REST_Request( 'POST', '/kudos/v1/payment/webhook/stripe' );
		$request->set_param( 'vendor', 'stripe' );
		$response = $this->controller->handle_webhook( $request );

		$this->assertSame( 400, $response->get_status() );
		$this->assertFalse( $response->data['success'] );
	}

	/**
	 * An unrecognised vendor slug is rejected rather than silently mis-routed.
	 */
	public function test_unknown_vendor_returns_404(): void {
		$request = new WP_REST_Request( 'POST', '/kudos/v1/payment/webhook/nope' );
		$request->set_param( 'vendor', 'nope' );
		$response = $this->controller->handle_webhook( $request );

		$this->assertSame( 404, $response->get_status() );
	}
}