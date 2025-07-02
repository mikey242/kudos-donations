<?php
/**
 * Plugin tests
 */

namespace Controller\Rest;

use BaseTestCase;
use Dompdf\Dompdf;
use IseardMedia\Kudos\Repository\DonorRepository;
use IseardMedia\Kudos\Repository\TransactionRepository;
use IseardMedia\Kudos\Service\PDFService;
use IseardMedia\Kudos\Service\TwigService;
use IseardMedia\Kudos\Vendor\PaymentVendor\MolliePaymentVendor;
use IseardMedia\Kudos\ThirdParty\Monolog\Logger;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Invoice rest route related tests.
 */
class InvoiceTest extends BaseTestCase {

	private WP_REST_Request $request;

	public function set_up():void {
		parent::set_up();

		// Create PDFService dependencies
		$twig   = $this->createMock( TwigService::class );
		$dompdf = $this->createMock( Dompdf::class );
		$logger = $this->createMock( Logger::class );

		// Use PDFService to initialize required directories.
		$pdf = new PDFService( $twig, $dompdf );
		$pdf->setLogger( $logger );
		$pdf->on_plugin_activation();

		//Get repository.
		$transaction_repository = new TransactionRepository($this->wpdb);
		$donor_repository = new DonorRepository($this->wpdb);

		// Create Donor and link to Transaction.
		$donor_id = $donor_repository->save([
			DonorRepository::NAME => 'John Smith'
		]);

		$transaction_id = $transaction_repository->save(
			[
				TransactionRepository::DONOR_ID => $donor_id,
				TransactionRepository::VENDOR_PAYMENT_ID => 'tr_12345',
				TransactionRepository::VENDOR => MolliePaymentVendor::get_slug(),
				TransactionRepository::SEQUENCE_TYPE => 'oneoff',
				TransactionRepository::INVOICE_NUMBER => 1,
				TransactionRepository::CURRENCY => 'EUR',
				TransactionRepository::VALUE => '10',
			]
		);

		// Define Request.
		$this->request = new WP_REST_Request( WP_REST_Server::READABLE, "/kudos/v1/invoice/$transaction_id" );
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
