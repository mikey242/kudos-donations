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
			'/transaction/(?P<id>\d+)' => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_invoice' ],
				'permission_callback' => [$this, 'can_manage_options'],
			],
			'/view_sample'             => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'view_sample_invoice' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			],
		];
	}

	/**
	 * Generate an invoice for the supplied transaction id.
	 *
	 * @param WP_REST_Request $request The REST request.
	 */
	public function get_invoice( WP_REST_Request $request ) {
		$transaction_id = $request->get_param( 'id' );
		$file_name      = "invoice-$transaction_id.pdf";
		$file           = PDFService::INVOICE_DIR . $file_name;

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
			'order_id'        => TransactionPostType::get_formatted_id( (int) $transaction_id ),
			'currency'        => $transaction->{TransactionPostType::META_FIELD_CURRENCY},
			'sequence_type'   => $transaction->{TransactionPostType::META_FIELD_SEQUENCE_TYPE},
			'id'              => 'inv_' . $transaction_id,
			'date'            => $transaction->post_date,
			'company_name'    => get_option( '_kudos_invoice_company_name' ),
			'company_address' => get_option( '_kudos_invoice_company_address' ),
			'vat_number'      => get_option( '_kudos_invoice_vat_number' ),
			'currency_symbol' => html_entity_decode( Utils::get_currency_symbol( 'EUR' ) ),
			'items'           => [
				'One-off'                      => number_format_i18n( $transaction->{TransactionPostType::META_FIELD_VALUE}, 2 ),
				__( 'VAT', 'kudos-donations' ) => 0,
			],
			'total'           => number_format_i18n( $transaction->{TransactionPostType::META_FIELD_VALUE}, 2 ),
		];

		$this->pdf->generate( $file, 'pdf/invoice.html.twig', $data, true );
		exit;
	}


	/**
	 * Generates and streams a sample invoice.
	 */
	public function view_sample_invoice(): void {

		$file_name = 'invoice-SAMPLE.pdf';
		$file      = PDFService::INVOICE_DIR . $file_name;

		$data = [
			'donor_name'      => 'John Smith',
			'donor_street'    => '123 Sample Street',
			'donor_postcode'  => '9876SP',
			'donor_city'      => 'Sampleton',
			'order_id'        => 'kdo_SAMPLE',
			'currency'        => 'EUR',
			'sequence_type'   => 'oneoff',
			'id'              => 'inv_' . 1001,
			'date'            => time(),
			'company_name'    => get_option( '_kudos_invoice_company_name' ),
			'company_address' => get_option( '_kudos_invoice_company_address' ),
			'vat_number'      => get_option( '_kudos_invoice_vat_number' ),
			'currency_symbol' => html_entity_decode( Utils::get_currency_symbol( 'EUR' ) ),
			'items'           => [
				'One-off'                      => number_format_i18n( 20, 2 ),
				__( 'VAT', 'kudos-donations' ) => 0,
			],
			'total'           => number_format_i18n( 20, 2 ),
		];

		$this->pdf->generate( $file, 'pdf/invoice.html.twig', $data, true );
	}
}
