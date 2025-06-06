<?php
/**
 * Plugin tests
 */

namespace Controller\Rest;

use Dompdf\Dompdf;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Service\PDFService;
use IseardMedia\Kudos\Service\TwigService;
use IseardMedia\Kudos\Vendor\PaymentVendor\MolliePaymentVendor;
use IseardMedia\Kudos\ThirdParty\Monolog\Logger;
use WP_REST_Request;
use WP_REST_Server;
use WP_UnitTestCase;

/**
 * Invoice rest route related tests.
 */
class Invoice extends WP_UnitTestCase {

	private WP_REST_Request $request;

	public function set_up() {

		// Create PDFService dependencies
		$twig   = $this->createMock( TwigService::class );
		$dompdf = $this->createMock( Dompdf::class );
		$logger = $this->createMock( Logger::class );

		// Use PDFService to initialize required directories.
		$pdf = new PDFService( $twig, $dompdf );
		$pdf->setLogger( $logger );
		$pdf->on_plugin_activation();

		// Create Donor and link to Transaction.
		$donor_id = $this->factory()->post->create(
			[
				'post_status' => 'publish',
				'post_type'   => DonorPostType::get_slug(),
			]
		);
		update_post_meta( $donor_id, DonorPostType::META_FIELD_NAME, 'John Smith' );

		$transaction_id = $this->factory()->post->create(
			[
				'post_status' => 'publish',
				'post_type'   => TransactionPostType::get_slug(),
			]
		);
		update_post_meta( $transaction_id, TransactionPostType::META_FIELD_DONOR_ID, $donor_id );
		update_post_meta( $transaction_id, TransactionPostType::META_FIELD_VENDOR_PAYMENT_ID, 'tr_12345' );
		update_post_meta( $transaction_id, TransactionPostType::META_FIELD_VENDOR, MolliePaymentVendor::get_slug() );
		update_post_meta( $transaction_id, TransactionPostType::META_FIELD_SEQUENCE_TYPE, 'oneoff' );
		update_post_meta( $transaction_id, TransactionPostType::META_FIELD_INVOICE_NUMBER, 1 );
		update_post_meta( $transaction_id, TransactionPostType::META_FIELD_CURRENCY, 'EUR' );
		update_post_meta( $transaction_id, TransactionPostType::META_FIELD_VALUE, '10' );

		// Define Request.
		$this->request = new WP_REST_Request( WP_REST_Server::READABLE, "/kudos/v1/invoice/get/transaction/$transaction_id" );
	}

	/**
	 * Test invoice route not public.
	 */
	public function test_route_protected() {
		wp_set_current_user( 0 );
		$request  = $this->request;
		$response = rest_do_request( $request );
		$status   = $response->get_status();
		$this->assertEquals( 401, $status );
	}

	/**
	 * Test getting invoice.
	 */
	public function test_get_invoice() {
		$admin_id = $this->factory()->user->create(
			[
				'role' => 'administrator',
			]
		);
		wp_set_current_user( $admin_id );
		$request  = $this->request;
		$response = rest_do_request( $request );
		$status   = $response->get_status();
		$this->assertEquals( 200, $status );
	}
}
