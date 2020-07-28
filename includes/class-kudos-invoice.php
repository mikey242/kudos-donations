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
	private $transactionArray;
	/**
	 * @var mixed|string
	 */
	private $order_id;
	/**
	 * @var mixed|string
	 */
	private $value;
	/**
	 * @var mixed|string
	 */
	private $sequence_type;
	/**
	 * @var bool|mixed
	 */
	private $refunds;
	/**
	 * @var mixed|string
	 */
	private $id;

	/**
	 * Kudos_Invoice constructor.
	 *
	 * @param Transaction $transaction
	 *
	 * @since    1.1.0
	 */
	public function __construct($transaction) {

		$this->logger = new Kudos_Logger();
		$this->twig = new Kudos_Twig();
		$this->pdf = new Dompdf();
		$this->pdf->setPaper('A4');
		$this->enabled = get_option('_kudos_invoice_enable');
		$this->id = $transaction->fields['id'] + 1000;
		$this->order_id = $transaction->fields['order_id'];
		$this->value = number_format_i18n($transaction->fields['value'], 2);
		$this->sequence_type = $transaction->fields['sequence_type'];
		$this->refunds = $transaction->get_refunds();

		$donor = $transaction->get_donor();

		$this->transactionArray = [
			'date' => $transaction->fields['transaction_created'],
			'order_id' => $this->order_id,
			'logo' => 'data:image/svg+xml;base64,'. base64_encode('<svg xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" clip-rule="evenodd" viewBox="0 0 555 449"><defs/><path fill="#2ec4b6" d="M0-.003h130.458v448.355H.001z"/><path fill="#ff9f1c" d="M489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/></svg>'),
			'logo_small' => 'data:image/svg+xml;base64,'. base64_encode('<svg viewBox="0 0 555 449" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#a8aaaf" stroke-linejoin="round" stroke-miterlimit="2"><path d="M0-.003h130.458v448.355H.001zM489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/></svg>'),
			'company_name' => get_option('_kudos_invoice_company_name'),
			'company_address' => get_option('_kudos_invoice_company_address'),
			'vat_number' => get_option('_kudos_invoice_vat_number'),
			'currency_symbol' => html_entity_decode(get_currency_symbol($transaction->fields['currency'])),
			'donor_name' => $donor->fields['name'],
			'donor_street' => $donor->fields['street'],
			'donor_postcode' => $donor->fields['postcode'],
			'donor_city' => $donor->fields['city'],
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
	 * Generates pdf invoice for given transaction
	 *
	 * @param bool $overwrite
	 * @param bool $display
	 *
	 * @return bool|string
	 * @since    1.1.0
	 */
	public function generate_invoice($overwrite=false, $display=false) {

		if(!$this->enabled || !$this->isWriteable()) {
			return false;
		}

		$file = self::INVOICE_DIR . 'invoice-'. $this->order_id .'.pdf';

		if(file_exists($file) && !$overwrite) {
			return false;
		}

		$invoiceArray = [
			'id'    => 'inv_' . $this->id,
			'items' => [
				get_sequence_type($this->sequence_type) => $this->value,
				__('VAT', 'kudos-donations') => 0,
			],
			'total' => $this->value
		];

		$invoiceArray = array_merge($this->transactionArray, $invoiceArray);

		try {
			$dompdf = $this->pdf;
			$dompdf->loadHtml(
				$this->twig->render('pdf/invoice.html.twig', $invoiceArray)
			);

			$dompdf->render();
			$pdf = $dompdf->output();

			if($display) {
				$dompdf->stream();
			}

			if(file_put_contents($file, $pdf)) {
				$this->logger->debug('Invoice successfully generated', ['file' => $file]);
				return $file;
			}

		} catch (Throwable $e) {
			$this->logger->critical($e->getMessage());
			return false;
		}

		return false;
	}

	/**
	 * Generate refund pdf from transaction with refunds
	 *
	 * @param bool $display
	 * @return bool|string
	 * @since   2.0.0
	 */
	public function generate_refund($display=false) {

		if(!$this->enabled || !$this->isWriteable()) {
			return false;
		}

		$refunds = $this->refunds;
		if(NULL == $refunds) {
			return false;
		}

		$dompdf = $this->pdf;
		$twig = $this->twig;

		$file = self::INVOICE_DIR . 'refund-'. $this->order_id .'.pdf';

		$refundArray = [
			'items' => [
				__('Original', 'kudos-donations') => $this->value,
				__('Refund', 'kudos-donations') => number_format_i18n($refunds['refunded'], 2),
			],
			'total' => number_format_i18n($refunds['remaining'], 2),
		];

		$refundArray = array_merge($this->transactionArray, $refundArray);

		try {

			$dompdf->loadHtml(
				$twig->render('pdf/refund.html.twig', $refundArray)
			);

			$dompdf->render();
			$pdf = $dompdf->output();

			if($display) {
				$dompdf->stream();
			}

			if(file_put_contents($file, $pdf)) {
				$this->logger->debug('Refund successfully generated', ['file' => $file]);
				return $file;
			}

		} catch (Throwable $e) {
			$this->logger->critical($e->getMessage());
			return false;
		}

		return false;

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

		$transaction = new Transaction();
		$transactions = $transaction->get_all();
		$n=0;

		/** @var Transaction $transaction */
		foreach ($transactions as $transaction) {
			if($transaction->fields['status'] === 'paid') {
				$invoice = new Kudos_Invoice($transaction);
				$invoice->generate_invoice(true) ? $n++ : null;
			}
		}

		return $n;
	}

}