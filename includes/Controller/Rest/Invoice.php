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

use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Service\MailerService;
use IseardMedia\Kudos\Service\PDFService;
use IseardMedia\Kudos\Service\SettingsService;
use WP_REST_Request;
use WP_REST_Server;

class Invoice extends AbstractRestController {

	/**
	 * Mailer service.
	 *
	 * @var MailerService
	 */
	private MailerService $mailer_service;

	/**
	 * PaymentRoutes constructor.
	 *
	 * @param PDFService $pdf Mailer service.
	 */
	public function __construct( PDFService $pdf ) {
		parent::__construct();

		$this->rest_base = 'invoice';
		$this->pdf       = $pdf;
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
					'force_generate' => [
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
					'force_generate' => [
						'type'     => 'boolean',
						'required' => false,
						'default'  => false,
					],
				],
			],
			'/view_sample'                  => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'view_sample_invoice' ],
				'permission_callback' => [ $this, 'can_manage_options' ],
			],
		];
	}

	/**
	 * Generate an invoice for the supplied transaction id.
	 *
	 * @param int  $transaction_id The transaction id to use.
	 * @param bool $force_generate Whether to regenerate even if existing pdf found.
	 */
	private function generate_invoice( int $transaction_id, bool $force_generate = false ) {
		$file_name = "invoice-$transaction_id.pdf";
		$file      = PDFService::INVOICE_DIR . $file_name;

		// Stream existing file if found.
		if ( ! $force_generate ) {
			if ( file_exists( $file ) ) {
				return;
			}
		}

		$transaction = TransactionPostType::get_post( [ 'ID' => $transaction_id ] );

		if ( ! $transaction ) {
			wp_send_json_error( [ 'message' => 'Transaction not found' ] );
			return;
		}

		$donor = DonorPostType::get_post( [ 'ID' => $transaction->{TransactionPostType::META_FIELD_DONOR_ID} ] );

		if ( ! $donor ) {
			wp_send_json_error( [ 'message' => 'Donor not found' ] );
			return;
		}

		$data = [
			'donor_name'      => $donor->{DonorPostType::META_FIELD_NAME},
			'donor_street'    => $donor->{DonorPostType::META_FIELD_STREET},
			'donor_postcode'  => $donor->{DonorPostType::META_FIELD_POSTCODE},
			'donor_city'      => $donor->{DonorPostType::META_FIELD_CITY},
			'order_id'        => $transaction->{TransactionPostType::META_FIELD_VENDOR_PAYMENT_ID},
			'vendor'          => $transaction->{TransactionPostType::META_FIELD_VENDOR},
			'currency'        => $transaction->{TransactionPostType::META_FIELD_CURRENCY},
			'sequence_type'   => $transaction->{TransactionPostType::META_FIELD_SEQUENCE_TYPE},
			'id'              => 'inv_' . $transaction->{TransactionPostType::META_FIELD_INVOICE_NUMBER},
			'date'            => $transaction->post_date,
			'company_name'    => get_bloginfo( 'name' ),
			'company_address' => get_option( SettingsService::SETTING_NAME_INVOICE_COMPANY_ADDRESS ),
			'vat_number'      => get_option( SettingsService::SETTING_NAME_INVOICE_VAT_NUMBER ),
			'currency_symbol' => html_entity_decode( Utils::get_currency_symbol( 'EUR' ) ),
			'items'           => [
				'One-off'                      => number_format_i18n( $transaction->{TransactionPostType::META_FIELD_VALUE}, 2 ),
				__( 'VAT', 'kudos-donations' ) => 0,
			],
			'total'           => number_format_i18n( $transaction->{TransactionPostType::META_FIELD_VALUE}, 2 ),
		];

		$this->pdf->generate( $file, 'pdf/invoice.html.twig', $data );
	}

	/**
	 * Generate an invoice for the supplied transaction id.
	 *
	 * @param WP_REST_Request $request The REST request.
	 */
	public function view_invoice( WP_REST_Request $request ) {
		$transaction_id = $request->get_param( 'id' );
		$force_generate = $request->get_param( 'force_generate' );

		$this->generate_invoice( (int) $transaction_id, $force_generate );

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
		$force_generate = $request->get_param( 'force_generate' );

		$this->generate_invoice( (int) $transaction_id, $force_generate );

		$file_name = "invoice-$transaction_id.pdf";
		$file      = PDFService::INVOICE_DIR . $file_name;
		wp_send_json( $file );
	}
}
