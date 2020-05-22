<?php

namespace Kudos;

use Dompdf\Dompdf;
use Throwable;

class Kudos_Invoice
{

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

	const INVOICE_DIR = KUDOS_DIR . 'invoices/';

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

		if(!file_exists(self::INVOICE_DIR)) {
			mkdir(self::INVOICE_DIR, 0755, true);
		}
	}

	/**
	 * Generates pdf invoice for given transaction
	 *
	 * @param object $transaction
	 * @param bool $display
	 *
	 * @return bool|string
	 * @since    1.1.0
	 */
	public function generate_invoice($transaction, $display=false) {

		$dompdf = $this->pdf;
		$twig = $this->twig;

		$order_id = $transaction->order_id;
		$filename = self::INVOICE_DIR . 'invoice-'. $order_id .'.pdf';

		try {
			$dompdf->loadHtml(
				$twig->render('pdf/invoice.html.twig', [
					'logo' => 'data:image/svg+xml;base64,'. base64_encode('<svg xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" clip-rule="evenodd" viewBox="0 0 555 449"><defs/><path fill="#2ec4b6" d="M0-.003h130.458v448.355H.001z"/><path fill="#ff9f1c" d="M489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/></svg>'),
					'date' => $transaction->time,
					'description' => __('One-off donation', 'kudos-donations'),
					'amount' => (!empty($transaction->currency) ? html_entity_decode(get_currency_symbol($transaction->currency)) : '') . number_format_i18n($transaction->value, 2),
					'order_id' => $order_id,
					'website_name' => get_bloginfo('name'),
				])
			);

			$dompdf->render();

			$pdf = $dompdf->output();

			if($display) {
				$dompdf->stream();
			}

		} catch (Throwable $e) {
			$this->logger->log($e->getMessage(), 'CRITICAL');
			return false;
		}

		if(file_put_contents($filename, $pdf)) {
			return $filename;
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
	 * @since   1.1.0
	 */
	public function get_invoice($order_id, $path=false) {

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
	 * Regenerates all invoices from the twig template
	 *
	 * @since   1.1.0
	 */
	public static function regenerate_invoices() {

		global $wpdb;

		$table = $wpdb->prefix . Kudos_Transaction::TABLE;
		$query = "
			SELECT * from $table
		";

		$transactions = $wpdb->get_results($query);
		$n=0;

		foreach ($transactions as $transaction) {
			$invoice = new Kudos_Invoice();
			$invoice->generate_invoice($transaction) ? $n++ : null;
		}

		return $n;
	}

}