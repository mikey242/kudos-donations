<?php
/**
 * PDF Generating service.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Infrastructure\Container\AbstractService;
use Throwable;

class PDFService extends AbstractService {

	public const INVOICE_DIR = KUDOS_STORAGE_DIR . 'invoices/';
	public const INVOICE_URL = KUDOS_STORAGE_URL . 'invoices/';
	private const FONTS_DIR  = KUDOS_CACHE_DIR . 'fonts/';
	private Dompdf $pdf;
	private TwigService $twig;
	private array $logos;

	/**
	 * Pdf constructor.
	 *
	 * @param TwigService $twig TwigService.
	 * @param Dompdf      $pdf PDF generator.
	 */
	public function __construct( TwigService $twig, Dompdf $pdf ) {

		$this->twig = $twig;
		$this->pdf  = $pdf;

		// Config DomPdf.
		$options = new Options();
		$options->setFontDir( self::FONTS_DIR );
		$options->setFontCache( self::FONTS_DIR );
		$options->setFontHeightRatio( 1 );
		$options->setIsFontSubsettingEnabled( true );
		$options->setDefaultPaperSize( 'A4' );
		$this->pdf->setOptions( $options );

		$this->logos = [
			'logo' => 'data:image/svg+xml,' . Utils::get_logo_svg(),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->init();
	}

	/**
	 * Create the invoice and font cache directories.
	 */
	private function init() {
		wp_mkdir_p( self::INVOICE_DIR );
		wp_mkdir_p( self::FONTS_DIR );
	}

	/**
	 * Checks if invoice file is writeable and returns true if it is.
	 */
	public function is_writeable(): bool {
		if ( wp_is_writable( self::INVOICE_DIR ) ) {
			return true;
		}

		$this->logger->warning( 'Invoice directory not writeable', [ 'path' => self::INVOICE_DIR ] );

		return false;
	}

	/**
	 * Streams the provided PDF.
	 *
	 * @param string $file The full path to the file.
	 */
	public function stream( string $file ): void {
		if ( file_exists( $file ) ) {
			if ( ob_get_contents() ) {
				ob_end_clean();
			}
			$file_name = basename( $file );
			$response  = wp_remote_get( self::INVOICE_URL . $file_name );
			if ( is_wp_error( $response ) ) {
				$this->logger->warning( $response->get_error_message() );
				return;
			}
			$body = wp_remote_retrieve_body( $response );
			header( 'Content-type: application/pdf' );
			header( "Content-disposition: inline;filename=$file_name" );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $body;
			exit;
		}
		$this->logger->debug( 'Unable to steam, file not found', [ 'file' => $file ] );
	}

	/**
	 * Generates and outputs / streams the pdf.
	 *
	 * @param string $file The output file.
	 * @param string $template Template to use.
	 * @param array  $data Data to pass to template.
	 * @return bool
	 */
	public function generate( string $file, string $template, array $data ) {

		$data = array_merge( $data, [ 'logos' => $this->logos ] );

		try {
			$dompdf = new Dompdf( $this->pdf->getOptions() );
			$dompdf->loadHtml(
				$this->twig->render( $template, $data )
			);

			$dompdf->render();

			$pdf = $dompdf->output();

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			if ( file_put_contents( $file, $pdf ) > 0 ) {
				$this->logger->debug( 'PDF successfully generated', [ 'file' => $file ] );

				return $file;
			}

			return false;

		} catch ( Throwable $e ) {
			$this->logger->critical( $e->getMessage(), $data );

			return false;
		}
	}
}
