<?php

namespace Kudos;

use Dompdf\Dompdf;
use Kudos\Entity\Transaction;
use Throwable;

class Kudos_Invoice
{
	const INVOICE_DIR = KUDOS_DIR . 'invoices/';
	/**
	 * @var Kudos_Logger
	 */
	private $logger;
	/**
	 * @var Dompdf
	 */
	private $pdf;
	/**
	 * @var Kudos_Twig
	 */
	private $twig;
	/**
	 * @var bool|mixed|void
	 */
	private $enabled;
	/**
	 * @var array
	 */
	private $logos;
	/**
	 * @var bool
	 */
	private $overwrite;
	/**
	 * @var bool
	 */
	private $display;

	/**
	 * Kudos_Invoice constructor.
	 *
	 * @since    1.1.0
	 */
	public function __construct() {

		$this->logger = new Kudos_Logger();
		$this->twig = new Kudos_Twig();
		$this->pdf = new Dompdf();
		$this->pdf->setPaper('A4');
		$this->enabled = get_option('_kudos_invoice_enable');
		$this->overwrite = true;
		$this->display = false;
		$this->logos = [
			'logo' => 'data:image/svg+xml;base64,'. base64_encode('<svg xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" clip-rule="evenodd" viewBox="0 0 555 449"><defs/><path fill="#2ec4b6" d="M0-.003h130.458v448.355H.001z"/><path fill="#ff9f1c" d="M489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/></svg>'),
			'logo_small' => 'data:image/svg+xml;base64,'. base64_encode('<svg viewBox="0 0 555 449" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#a8aaaf" stroke-linejoin="round" stroke-miterlimit="2"><path d="M0-.003h130.458v448.355H.001zM489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/></svg>'),
		];

		if(!file_exists(self::INVOICE_DIR)) {
			wp_mkdir_p(self::INVOICE_DIR);
		}

	}

	/**
	 * Checks if invoice file is writeable and returns true if it is
	 *
	 * @since   2.0.0
	 * @return bool
	 */
	public static function isWriteable() {
		if(is_writable(self::INVOICE_DIR)) {
			return true;
		}
		return false;
	}

	/**
	 * Generates and outputs or streams the pdf
	 *
	 * @param string $file
	 * @param string $template
	 * @param array $data
	 * @return bool
	 * @since   2.0.0
	 */
	private function generate($file, $template, $data) {

		$data = array_merge($data, ['logos' => $this->logos]);

		try {
			$dompdf = $this->pdf;
			$dompdf->loadHtml(
				$this->twig->render($template, $data)
			);

			$dompdf->render();

			if($this->display) {
				ob_end_clean();
				$dompdf->stream($file, ['Attachment' => false]);
			}

			if(file_exists($file) && !$this->overwrite) {
				return false;
			}

			$pdf = $dompdf->output();

			if(file_put_contents($file, $pdf)) {
				$this->logger->debug('Invoice successfully generated', ['file' => $file]);
				return $file;
			}
			return false;

		} catch (Throwable $e) {
			$this->logger->critical($e->getMessage());
			return false;
		}
	}

	/**
	 * Generates pdf invoice for given transaction
	 *
	 * @param Transaction $transaction
	 * @param bool $overwrite
	 * @param bool $display
	 * @return bool|string
	 * @since    1.1.0
	 */
	public function generate_invoice($transaction, $overwrite=false, $display=false) {

		if(!$this->enabled || !$this->isWriteable()) {
			return false;
		}

		$this->overwrite = $overwrite;
		$this->display = $display;

		$file_name = 'invoice-'. $transaction->order_id .'.pdf';
		$file = self::INVOICE_DIR . $file_name;
		$donor = $transaction->get_donor();

		$invoiceArray = [
			'id' => 'inv_' . ($transaction->id + 1000),
			'date' => $transaction->created,
			'order_id' => $transaction->order_id,
			'company_name' => get_option('_kudos_invoice_company_name'),
			'company_address' => get_option('_kudos_invoice_company_address'),
			'vat_number' => get_option('_kudos_invoice_vat_number'),
			'currency_symbol' => html_entity_decode(get_currency_symbol($transaction->currency)),
			'donor_name' => $donor->name,
			'donor_street' => $donor->street,
			'donor_postcode' => $donor->postcode,
			'donor_city' => $donor->city,
			'items' => [
				get_sequence_type($transaction->sequence_type) => number_format_i18n($transaction->value, 2),
				__('VAT', 'kudos-donations') => 0,
			],
			'total' => number_format_i18n($transaction->value, 2)
		];

		return $this->generate($file, 'pdf/invoice.html.twig', $invoiceArray);
	}

	/**
	 * Generate refund pdf from transaction with refunds
	 *
	 * @param Transaction $transaction
	 * @param bool $display
	 * @return bool|string
	 * @since   2.0.0
	 */
	public function generate_refund($transaction, $display=false) {

		if(!$this->enabled || !$this->isWriteable()) {
			return false;
		}

		$refunds = $transaction->get_refund();
		if(NULL == $refunds) {
			return false;
		}

		$file_name = 'refund-'. $transaction->order_id .'.pdf';
		$file = self::INVOICE_DIR . $file_name;
		$donor = $transaction->get_donor();

		$refundArray = [
			'id' => 'inv_' . ($transaction->id + 1000),
			'date' => $transaction->created,
			'order_id' => $transaction->order_id,
			'company_name' => get_option('_kudos_invoice_company_name'),
			'company_address' => get_option('_kudos_invoice_company_address'),
			'vat_number' => get_option('_kudos_invoice_vat_number'),
			'currency_symbol' => html_entity_decode(get_currency_symbol($transaction->currency)),
			'donor_name' => $donor->name,
			'donor_street' => $donor->street,
			'donor_postcode' => $donor->postcode,
			'donor_city' => $donor->city,
			'items' => [
				__('Original', 'kudos-donations') => number_format_i18n($transaction->value, 2),
				__('Refund', 'kudos-donations') => number_format_i18n($refunds['refunded'], 2),
			],
			'total' => number_format_i18n($refunds['remaining'], 2),
		];

		return $this->generate($file, 'pdf/refund.html.twig', $refundArray);

	}

	public function view_sample_invoice() {

		$this->display = true;

		$file_name = 'invoice-SAMPLE.pdf';
		$file = self::INVOICE_DIR . $file_name;

		$data = [
			'donor_name' => 'John Smith',
			'donor_street' => '123 Sample Street',
			'donor_postcode' => '9876SP',
			'donor_city' => 'Sampleton',
			'order_id' => 'kdo_SAMPLE',
			'currency' => 'EUR',
			'sequence_type' => 'oneoff',
			'id' => 'inv_' . 1001,
			'date' => time(),
			'company_name' => get_option('_kudos_invoice_company_name'),
			'company_address' => get_option('_kudos_invoice_company_address'),
			'vat_number' => get_option('_kudos_invoice_vat_number'),
			'currency_symbol' => html_entity_decode(get_currency_symbol('EUR')),
			'items' => [
				'One-off' => number_format_i18n(20, 2),
				__('VAT', 'kudos-donations') => 0,
			],
			'total' => number_format_i18n(20, 2)
		];

		$this->generate($file, 'pdf/invoice.html.twig', $data);
	}

	/**
	 * Returns the invoice url or path for the specified order_id
	 *
	 * @param string $order_id
	 * @param bool $path
	 *
	 * @return bool|string
	 * @since   2.0.0
	 */
	public static function get_invoice($order_id, $path=false) {

		$filename = 'invoice-'. $order_id .'.pdf';
		$file = self::INVOICE_DIR . $filename;

		if(file_exists($file)) {
			if(!$path) {
				return plugin_dir_url( dirname( __FILE__ ) ) . 'invoices/' . $filename;
			}
			return $file;
		}

		return false;
	}

	/**
	 * Returns the refund url or path for the specified order_id
	 *
	 * @param string $order_id
	 * @param bool $path
	 *
	 * @return bool|string
	 * @since   2.0.0
	 */
	public static function get_refund($order_id, $path=false) {

		$filename = 'refund-'. $order_id .'.pdf';
		$file = self::INVOICE_DIR . $filename;

		if(file_exists($file)) {
			if(!$path) {
				return plugin_dir_url( dirname( __FILE__ ) ) . 'invoices/' . $filename;
			}
			return $file;
		}

		return false;
	}

	/**
	 * Regenerates all paid invoices from the twig template
	 *
	 * @since   2.0.0
	 */
	public static function regenerate_invoices() {

		$mapper = new Mapper(Transaction::class);
		$transactions = $mapper->get_all_by();
		$n=0;

		/** @var Transaction $transaction */
		foreach ($transactions as $transaction) {
			if($transaction->status === 'paid') {
				$invoice = new Kudos_Invoice();
				$invoice->generate_invoice($transaction, true) ? $n++ : null;
			}
		}

		return $n;
	}

}