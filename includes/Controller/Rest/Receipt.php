<?php
/**
 * Receipt Rest Routes.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Service\PDFService;
use IseardMedia\Kudos\Service\ReceiptService;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Receipt extends BaseRestController {

	private ReceiptService $invoice;
	private PDFService $pdf;
	private TransactionRepository $transaction_repository;

	/**
	 * PaymentRoutes constructor.
	 *
	 * @param PDFService            $pdf Mailer service.
	 * @param ReceiptService        $invoice Receipt service.
	 * @param TransactionRepository $transaction_repository The transaction repository.
	 */
	public function __construct( PDFService $pdf, ReceiptService $invoice, TransactionRepository $transaction_repository ) {
		$this->rest_base              = 'receipt';
		$this->pdf                    = $pdf;
		$this->invoice                = $invoice;
		$this->transaction_repository = $transaction_repository;
	}

	/**
	 * Receipt service routes.
	 */
	public function get_routes(): array {
		return [
			'/(?P<id>\d+)' => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_receipt' ],
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
			'/regenerate'  => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'regenerate_receipts' ],
				'permission_callback' => [ $this, 'can_manage_options' ],
			],
		];
	}

	/**
	 * Generate a receipt for the supplied transaction id.
	 *
	 * @param WP_REST_Request $request The REST request.
	 */
	public function get_receipt( WP_REST_Request $request ): WP_REST_Response {
		$transaction_id = $request->get_param( 'id' );
		$force          = $request->get_param( 'force' ) ?? false;
		$view           = $request->get_param( 'view' );

		$file = $this->invoice->generate_receipt( (int) $transaction_id, $force );

		if ( null !== $file ) {
			if ( true === $view ) {
				$this->pdf->stream( $file );
			}
			return new WP_REST_Response( [ 'path' => $file ], 200 );
		}
		return new WP_REST_Response( [ 'message' => __( 'Something went wrong generating receipt', 'kudos-donations' ) ], 500 );
	}

	/**
	 * Regenerate all receipts.
	 */
	public function regenerate_receipts(): WP_REST_Response {
		$transactions = $this->transaction_repository->find_by( [ 'status' => PaymentStatus::PAID ] );
		if ( $transactions ) {
			foreach ( $transactions as $transaction ) {
				$this->invoice->generate_receipt( $transaction->id, true );
			}
			// translators: %s represents the number of invoices.
			return new WP_REST_Response( [ 'message' => \sprintf( __( 'Regenerated %s invoices successfully.', 'kudos-donations' ), \count( $transactions ) ) ], 200 );
		}
		return new WP_REST_Response( [ 'message' => __( 'No valid transactions.', 'kudos-donations' ) ], 200 );
	}
}
