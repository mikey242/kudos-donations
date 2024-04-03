<?php
/**
 * Invoice Service.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Helper\Utils;
use Psr\Log\LoggerInterface;

class InvoiceService extends AbstractService {

	private PDFService $pdf;
	private LoggerInterface $logger;

	public function __construct(PDFService $pdf, LoggerInterface $logger) {
		$this->pdf = $pdf;
		$this->logger = $logger;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		add_filter('kudos_receipt_attachment', [$this, 'attach_to_email'], 10, 2);
	}

	/**
	 * Callback for attaching invoice to email.
	 * @param $attachment
	 * @param $transaction_id
	 *
	 * @return array
	 */
	public function attach_to_email($attachment, $transaction_id): array {
		$file = $this->generate_invoice($transaction_id);
		$this->logger->debug('Adding invoice to email.', ['file' => $file]);
		$attachment[] = $file;
		return $attachment;
	}

	/**
	 * Generate an invoice for the supplied transaction id.
	 *
	 * @param int  $transaction_id The transaction id to use.
	 * @param bool $force_generate Whether to regenerate even if existing pdf found.
	 */
	public function generate_invoice( int $transaction_id, bool $force_generate = false ) {
		$file_name = "invoice-$transaction_id.pdf";
		$file      = PDFService::INVOICE_DIR . $file_name;

		// Stream existing file if found.
		if ( ! $force_generate ) {
			if ( file_exists( $file ) ) {
				return $file;
			}
		}

		$transaction = TransactionPostType::get_post( [ 'ID' => $transaction_id ] );

		if ( ! $transaction ) {
			wp_send_json_error( [ 'message' => 'Transaction not found' ] );
			return null;
		}

		$donor = DonorPostType::get_post( [ 'ID' => $transaction->{TransactionPostType::META_FIELD_DONOR_ID} ] );

		if ( ! $donor ) {
			wp_send_json_error( [ 'message' => 'Donor not found' ] );
			return null;
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

		return $this->pdf->generate( $file, 'pdf/invoice.html.twig', $data );
	}
}
