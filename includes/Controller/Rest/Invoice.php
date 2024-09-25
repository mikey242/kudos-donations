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

use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Service\InvoiceService;
use IseardMedia\Kudos\Service\PDFService;
use WP_REST_Request;
use WP_REST_Response;
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
			'/get/transaction/(?P<id>\d+)' => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_invoice' ],
				'permission_callback' => [ $this, 'can_manage_options' ],
				'args'                => [
					'force' => [
						'type'     => FieldType::BOOLEAN,
						'required' => false,
						'default'  => false,
					],
					'view'  => [
						'type'     => FieldType::BOOLEAN,
						'required' => false,
						'default'  => false,
					],
				],
			],
			'/regenerate'                  => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'regenerate_invoices' ],
				'permission_callback' => [ $this, 'can_manage_options' ],
			],
		];
	}

	/**
	 * Generate an invoice for the supplied transaction id.
	 *
	 * @param WP_REST_Request $request The REST request.
	 */
	public function get_invoice( WP_REST_Request $request ): WP_REST_Response {
		$transaction_id = $request->get_param( 'id' );
		$force          = $request->get_param( 'force' );
		$view           = $request->get_param( 'view' );

		$file = $this->invoice->generate_invoice( (int) $transaction_id, $force );

		if ( $file ) {
			if ( $view ) {
				$this->pdf->stream( $file );
			}
			return new WP_REST_Response( [ 'path' => $file ], 200 );
		} else {
			return new WP_REST_Response( [ 'message' => __( 'Something went wrong generating invoice', 'kudos-donations' ) ], 500 );
		}
	}

	/**
	 * Regenerate all invoices.
	 */
	public function regenerate_invoices(): WP_REST_Response {
		$transactions = TransactionPostType::get_posts( [ TransactionPostType::META_FIELD_STATUS => PaymentStatus::PAID ] );
		if ( $transactions ) {
			foreach ( $transactions as $transaction ) {
				$this->invoice->generate_invoice( $transaction->ID, true );
			}
			// translators: %s represents the number of invoices.
			return new WP_REST_Response( [ 'message' => wp_sprintf( __( 'Regenerated %s invoices successfully.', 'kudos-donations' ), \count( $transactions ) ) ], 200 );
		}
		return new WP_REST_Response( [ 'message' => __( 'No valid transactions.', 'kudos-donations' ) ], 200 );
	}
}
