<?php
/**
 * Receipt Service.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;

class ReceiptService extends AbstractRegistrable implements HasSettingsInterface {
	public const SETTING_INVOICE_VAT_NUMBER      = '_kudos_invoice_vat_number';
	public const SETTING_INVOICE_NUMBER          = '_kudos_invoice_number';
	public const SETTING_INVOICE_COMPANY_ADDRESS = '_kudos_invoice_company_address';
	private PDFService $pdf;
	private TransactionRepository $transaction_repository;
	private DonorRepository $donor_repository;

	/**
	 * ReceiptService constructor.
	 *
	 * @param PDFService            $pdf PDF service.
	 * @param TransactionRepository $transaction_repository Transaction repository.
	 * @param DonorRepository       $donor_repository Donor repository.
	 */
	public function __construct( PDFService $pdf, TransactionRepository $transaction_repository, DonorRepository $donor_repository ) {
		$this->pdf                    = $pdf;
		$this->transaction_repository = $transaction_repository;
		$this->donor_repository       = $donor_repository;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		add_filter( 'kudos_receipt_attachment', [ $this, 'attach_to_email' ], 10, 2 );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_settings(): array {
		return [
			self::SETTING_INVOICE_NUMBER          => [
				'type'              => FieldType::INTEGER,
				'show_in_rest'      => true,
				'default'           => 1,
				'sanitize_callback' => 'absint',
			],
			self::SETTING_INVOICE_COMPANY_ADDRESS => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
			],
			self::SETTING_INVOICE_VAT_NUMBER      => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
			],
		];
	}

	/**
	 * Callback for attaching receipt PDF to email.
	 *
	 * @param array $attachments Array of current attachments.
	 * @param int   $transaction_id Transaction ID.
	 */
	public function attach_to_email( array $attachments, int $transaction_id ): array {
		$file = $this->generate_receipt( $transaction_id );
		if ( $file ) {
			$this->logger->debug( 'Adding receipt PDF to email.', [ 'file' => $file ] );
			$attachments[] = $file;
		}
		return $attachments;
	}

	/**
	 * Generate a receipt for the supplied transaction id.
	 *
	 * @param int  $transaction_id The transaction id to use.
	 * @param bool $force_generate Whether to regenerate even if existing pdf found.
	 */
	public function generate_receipt( int $transaction_id, bool $force_generate = false ): ?string {
		$file_name = "receipt-$transaction_id.pdf";
		$file      = PDFService::INVOICE_DIR . $file_name;

		// Return existing file if found.
		if ( ! $force_generate ) {
			if ( file_exists( $file ) ) {
				$this->logger->debug( 'Receipt already exists.', [ 'file' => $file ] );
				return $file;
			}
		}

		/**
		 * Get transaction.
		 *
		 * @var ?TransactionEntity $transaction
		 */
		$transaction = $this->transaction_repository->get( $transaction_id );

		if ( null === $transaction ) {
			$this->logger->debug( 'Error generating receipt: Transaction not found', [ 'transaction_id' => $transaction_id ] );
			return null;
		}

		// Populate data array.
		$data = [
			'order_id'        => $transaction->vendor_payment_id,
			'vendor'          => $transaction->vendor,
			'sequence_type'   => $transaction->sequence_type,
			'id'              => gmdate( 'Y' ) . '_' . $transaction->invoice_number,
			'date'            => $transaction->created_at,
			'company_name'    => Utils::get_company_name(),
			'company_address' => get_option( self::SETTING_INVOICE_COMPANY_ADDRESS ),
			'vat_number'      => get_option( self::SETTING_INVOICE_VAT_NUMBER ),
			'currency_symbol' => Utils::get_currencies()[ $transaction->currency ] ?? '',
			'items'           => [
				$transaction->title            => number_format_i18n( $transaction->value, 2 ),
				__( 'VAT', 'kudos-donations' ) => 0,
			],
			'total'           => Utils::format_value_for_display( (string) $transaction->value ),
		];

		// Append donor.
		/** @var ?DonorEntity $donor */
		$donor = $this->donor_repository->find_one_by( [ 'id' => $transaction->donor_id ] );
		if ( null !== $donor ) {
			$locale = $donor->locale;
			if ( $locale ) {
				$this->logger->debug( "Switching locale to $locale" );
				// Switch to donor's locale if available.
				Utils::switch_locale( $locale );
			}
			$data['donor_business'] = $donor->business_name ?? '';
			$data['donor_name']     = $donor->name ?? '';
			$data['donor_street']   = $donor->street ?? '';
			$data['donor_postcode'] = $donor->postcode ?? '';
			$data['donor_city']     = $donor->city ?? '';
			$data['donor_country']  = $donor->country ?? '';
		}

		// Add text.
		$data['text'] = [
			'receipt'     => __( 'Receipt', 'kudos-donations' ),
			'date'        => __( 'Date', 'kudos-donations' ),
			'to'          => __( 'To', 'kudos-donations' ),
			'from'        => __( 'From', 'kudos-donations' ),
			'description' => __( 'Description', 'kudos-donations' ),
			'amount'      => __( 'Amount', 'kudos-donations' ),
			'total'       => __( 'Total', 'kudos-donations' ),
			'vat_number'  => __( 'VAT Number', 'kudos-donations' ),
			'created_by'  => __( 'Created by', 'kudos-donations' ),
		];

		// Restore locale to original state.
		restore_previous_locale();

		$this->logger->debug( 'Generating new receipt.', [ 'file' => $file ] );

		return $this->pdf->generate( $file, 'pdf/receipt.html.twig', $data );
	}
}
