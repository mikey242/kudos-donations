<?php

namespace IseardMedia\Kudos\Tests\Controller\Rest;

use IseardMedia\Kudos\Controller\Rest\BaseRestController;
use IseardMedia\Kudos\Controller\Rest\Payment;
use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Tests\BaseTestCase;
use ReflectionMethod;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Bot checks on payment creation, and the no-cache headers on Kudos REST responses.
 */
class PaymentCreateGuardsTest extends BaseTestCase {

	public function test_rejects_form_submitted_too_quickly(): void {
		$response = $this->create_payment( [ 'elapsed' => '1' ] );

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'Request invalid.', $response->get_data()['message'] );
	}

	public function test_accepts_form_open_long_enough(): void {
		$this->assertFalse( $this->is_bot( [ 'elapsed' => '12' ] ) );
	}

	public function test_accepts_submission_without_elapsed(): void {
		$this->assertFalse( $this->is_bot( [] ) );
	}

	public function test_rejects_filled_honeypot(): void {
		$response = $this->create_payment( [ 'elapsed' => '12', 'donation' => 'spam' ] );

		$this->assertSame( 400, $response->get_status() );
	}

	public function test_kudos_responses_are_not_cacheable(): void {
		$response = $this->prevent_caching( '/kudos/v1/payment/status' );

		$this->assertStringContainsString( 'no-store', $response->get_headers()['Cache-Control'] ?? '' );
	}

	public function test_other_responses_are_left_alone(): void {
		$response = $this->prevent_caching( '/wp/v2/posts' );

		$this->assertArrayNotHasKey( 'Cache-Control', $response->get_headers() );
	}

	private function prevent_caching( string $route ): WP_REST_Response {
		return BaseRestController::prevent_caching( new WP_REST_Response(), null, new WP_REST_Request( WP_REST_Server::READABLE, $route ) );
	}

	private function is_bot( array $values ): bool {
		$method = new ReflectionMethod( Payment::class, 'is_bot' );
		$method->setAccessible( true );

		return $method->invoke( $this->get_from_container( Payment::class ), $values );
	}

	private function create_payment( array $params ): WP_REST_Response {
		$campaign_id = $this->get_from_container( CampaignRepository::class )->insert( new CampaignEntity( [ 'title' => 'Test' ] ) );

		$request = new WP_REST_Request( WP_REST_Server::CREATABLE, '/kudos/v1/payment/create' );
		$request->set_body_params(
			array_merge(
				[
					'campaign_id' => (string) $campaign_id,
					'value'       => '10',
					'name'        => 'Jane Donor',
					'recurring'   => 'false',
				],
				$params
			)
		);

		return rest_do_request( $request );
	}
}
