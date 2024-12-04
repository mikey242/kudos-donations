<?php
/**
 * PDF Generating service.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use IseardMedia\Kudos\Container\ActivationAwareInterface;
use IseardMedia\Kudos\Helper\Utils;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class PDFService implements ActivationAwareInterface, LoggerAwareInterface {

	use LoggerAwareTrait;

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

		// Config DomPdf.
		$options = new Options();
		$options->setFontDir( self::FONTS_DIR );
		$options->setFontCache( self::FONTS_DIR );
		$options->setFontHeightRatio( 1 );
		$options->setIsFontSubsettingEnabled( true );
		$options->setDefaultPaperSize( 'A4' );
		$pdf->setOptions( $options );
		$this->pdf = $pdf;

		$this->logos = [
			'logo' => 'data:image/svg+xml,' . Utils::get_company_logo_svg(),
		];
	}

	/**
	 * Create the invoice and font cache directories.
	 */
	public function on_plugin_activation(): void {
		if ( wp_mkdir_p( self::INVOICE_DIR ) && wp_mkdir_p( self::FONTS_DIR ) ) {
			$this->logger->info( 'Invoice directory created successfully.', [ 'location' => self::INVOICE_DIR ] );
		} else {
			$this->logger->info( 'Unable to create invoice directory.', [ 'location' => self::INVOICE_DIR ] );
		}
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
	 */
	public function generate( string $file, string $template, array $data ): ?string {

		// Check if target folder is writeable. If not it is possible that it does not yet exist.
		$target_dir = \dirname( $file );
		if ( ! wp_is_writable( $target_dir ) ) {
			wp_mkdir_p( $target_dir );
		}

		// Add logos to $data.
		$data = array_merge( $data, [ 'logos' => $this->logos ] );

		try {
			$dompdf = $this->pdf;
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

			return null;

		} catch ( Throwable $e ) {
			$this->logger->critical( $e->getMessage(), $data );

			return null;
		}
	}
}
