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
use Psr\Log\LoggerInterface;
use Throwable;

class PDFService extends AbstractService {

	public const INVOICE_DIR = KUDOS_STORAGE_DIR . 'invoices/';
	private const FONTS_DIR  = KUDOS_STORAGE_DIR . 'fonts/';

	private LoggerInterface $logger;
	private Dompdf $pdf;
	private TwigService $twig;
	private array $logos;

	/**
	 * Pdf constructor.
	 *
	 * @param LoggerInterface $logger Logger instance.
	 * @param TwigService     $twig TwigService.
	 * @param Dompdf          $pdf PDF generator.
	 * @param SettingsService $settings For getting settings.
	 */
	public function __construct( LoggerInterface $logger, TwigService $twig, Dompdf $pdf, SettingsService $settings ) {

		$this->logger = $logger;
		$this->twig   = $twig;
		$this->pdf    = $pdf;

		// Config DomPdf.
		$options = new Options();
		$options->setFontDir( self::FONTS_DIR );
		$options->setFontCache( self::FONTS_DIR );
		$options->setFontHeightRatio( 1 );
		$options->setIsFontSubsettingEnabled( true );
		$options->setDefaultPaperSize( 'A4' );
		$this->pdf->setOptions( $options );

		$this->logos = [
			'logo'       => 'data:image/svg+xml,' . Utils::get_logo_svg(),
			'logo_small' => 'data:image/svg+xml, <svg viewBox="0 0 555 449" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" fill="#a8aaaf" stroke-linejoin="round" stroke-miterlimit="2"><path d="M0-.003h130.458v448.355H.001zM489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/></svg>',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->init();
		add_action( 'kudos_process_transaction', [ $this, 'process_transaction' ] );
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
	 * Generates and outputs / streams the pdf.
	 *
	 * @param string $file The output file.
	 * @param string $template Template to use.
	 * @param array  $data Data to pass to template.
	 * @param bool   $display Whether to stream the output to screen.
	 * @return bool
	 */
	public function generate( string $file, string $template, array $data, bool $display = false ) {

		$data = array_merge( $data, [ 'logos' => $this->logos ] );

		try {
			$dompdf = new Dompdf( $this->pdf->getOptions() );
			$dompdf->loadHtml(
				$this->twig->render( $template, $data )
			);

			$dompdf->render();

			if ( $display ) {
				if ( ob_get_contents() ) {
					ob_end_clean();
				}
				$dompdf->stream( $file, [ 'Attachment' => false ] );
			}

			$pdf = $dompdf->output();

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
