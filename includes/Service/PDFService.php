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
		$options->setIsRemoteEnabled( true );
		$options->setDefaultFont( 'sans-serif' );
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
		if ( ! file_exists( $file ) ) {
			$this->logger->debug( 'Unable to stream, file not found', [ 'file' => $file ] );
			return;
		}

		while ( ob_get_level() ) {
			ob_end_clean();
		}

		$file_name     = basename( $file );
		$error_message = __( 'Error fetching invoice. Please check log for details.', 'kudos-donations' );
		$response      = wp_remote_get(
			self::INVOICE_URL . $file_name,
			[
				'headers' => [
					'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
					'Referer'    => home_url(),
				],
				'timeout' => 15,
			]
		);

		if ( is_wp_error( $response ) ) {
			NoticeService::add_notice( $error_message, NoticeService::ERROR );
			$this->logger->warning( $response->get_error_message() );
			return;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			NoticeService::add_notice( $error_message, NoticeService::ERROR );
			$this->logger->warning(
				'Failed to fetch PDF.',
				[
					'url'           => self::INVOICE_URL . $file_name,
					'response_code' => $response_code,
					'message'       => wp_remote_retrieve_response_message( $response ),
				]
			);
			return;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			NoticeService::add_notice( $error_message, NoticeService::ERROR );
			$this->logger->warning( 'Empty response body for PDF', [ 'url' => self::INVOICE_URL . $file_name ] );
			return;
		}

		header( 'Content-Type: application/pdf' );
		header( "Content-Disposition: inline; filename=\"$file_name\"" );
		header( 'Content-Length: ' . \strlen( $body ) );
		header( 'Accept-Ranges: bytes' );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $body;
		exit;
	}

	/**
	 * Generates and outputs / streams the pdf.
	 *
	 * @param string $file The output file.
	 * @param string $template Template to use.
	 * @param array  $data Data to pass to template.
	 */
	public function generate( string $file, string $template, array $data ): ?string {

		$target_dir = \dirname( $file );
		wp_mkdir_p( $target_dir );

		// Check if target folder is writeable.
		if ( ! wp_is_writable( $target_dir ) ) {
			NoticeService::add_notice( __( 'Cannot generate PDF. Directory not writeable', 'kudos-donations' ) . ': ' . $target_dir, NoticeService::ERROR );
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
