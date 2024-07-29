<?php
/**
 * Invoice Rest Routes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Service\InvoiceService;
use IseardMedia\Kudos\Service\PDFService;
use WP_REST_Request;
use WP_REST_Server;

class Invoice extends AbstractRestController {

	private InvoiceService $invoice;

	/**
	 * PaymentRoutes constructor.
	 *
	 * @param PDFService     $pdf Mailer service.
	 * @param InvoiceService $invoice Invoice service.
	 */
	public function __construct( PDFService $pdf, InvoiceService $invoice ) {
		parent::__construct();

		$this->rest_base = 'invoice';
		$this->pdf       = $pdf;
		$this->invoice   = $invoice;
	}

	/**
	 * Mail service routes.
	 */
	public function get_routes(): array {
		return [
			'/view/transaction/(?P<id>\d+)' => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'view_invoice' ],
				'permission_callback' => [ $this, 'can_manage_options' ],
				'args'                => [
					'force' => [
						'type'     => 'boolean',
						'required' => false,
						'default'  => false,
					],
				],

			],
			'/get/transaction/(?P<id>\d+)'  => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_invoice' ],
				'permission_callback' => [ $this, 'can_manage_options' ],
				'args'                => [
					'force' => [
						'type'     => 'boolean',
						'required' => false,
						'default'  => false,
					],
				],
			],
		];
	}

	/**
	 * Generate an invoice for the supplied transaction id.
	 *
	 * @param WP_REST_Request $request The REST request.
	 */
	public function view_invoice( WP_REST_Request $request ) {
		$transaction_id = $request->get_param( 'id' );
		$force          = $request->get_param( 'force' );

		$this->invoice->generate_invoice( (int) $transaction_id, $force );

		$file_name = "invoice-$transaction_id.pdf";
		$file      = PDFService::INVOICE_DIR . $file_name;
		$this->pdf->stream( $file );
	}

	/**
	 * Generate an invoice for the supplied transaction id.
	 *
	 * @param WP_REST_Request $request The REST request.
	 */
	public function get_invoice( WP_REST_Request $request ) {
		$transaction_id = $request->get_param( 'id' );
		$force          = $request->get_param( 'force' );

		$this->invoice->generate_invoice( (int) $transaction_id, $force );

		$file_name = "invoice-$transaction_id.pdf";
		$file      = PDFService::INVOICE_DIR . $file_name;
		wp_send_json( $file );
	}
}