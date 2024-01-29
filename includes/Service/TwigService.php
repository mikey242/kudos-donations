<?php
/**
 * TwigService.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

namespace IseardMedia\Kudos\Service;

use FilesystemIterator;
use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Helper\Utils;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigService {

	public const CACHE_DIR = KUDOS_STORAGE_DIR . 'twig/cache/';
	private Environment $twig;
	private array $options;
	private LoggerInterface $logger;

	/**
	 * Twig constructor
	 *
	 * @param LoggerInterface $logger_service Logger instance.
	 */
	public function __construct( LoggerInterface $logger_service ) {
		$this->logger           = $logger_service;
		$this->options['cache'] = KUDOS_DEBUG ? false : self::CACHE_DIR;
		$this->options['debug'] = KUDOS_DEBUG;

		$this->twig = new Environment(
			new FilesystemLoader(
				apply_filters( 'kudos_twig_template_paths', [ KUDOS_PLUGIN_DIR . 'templates/' ] )
			),
			$this->options
		);

		$this->initialize_twig_extensions();
		$this->initialize_twig_functions();
		$this->initialize_twig_filters();
	}

	/**
	 * Initialize additional twig extensions
	 */
	public function initialize_twig_extensions(): void {
		if ( KUDOS_DEBUG ) {
			$this->twig->addExtension( new DebugExtension() );
		}
	}

	/**
	 * Initialize additional twig functions
	 *
	 * @source https://wordpress.stackexchange.com/questions/287988/use-str-to-translate-strings-symfony-twig
	 */
	public function initialize_twig_functions(): void {
		/**
		 * Add gettext __ function.
		 */
		$get_text = new TwigFunction( '__', '__' );
		$this->twig->addFunction( $get_text );

		/**
		 * Add gettext _n function.
		 */
		$get_text_n = new TwigFunction( '_n', '_n' );
		$this->twig->addFunction( $get_text_n );

		/**
		 * Add get_option function.
		 */
		$get_option = new TwigFunction( 'get_option', 'get_option' );
		$this->twig->addFunction( $get_option );

		/**
		 * Add get_asset function.
		 */
		$get_asset = new TwigFunction( 'get_asset', [ Assets::class, 'get_style' ] );
		$this->twig->addFunction( $get_asset );

		/**
		 * Add generate_id function.
		 */
		$generate_id = new TwigFunction( 'generate_id', [ Utils::class, 'generate_id' ] );
		$this->twig->addFunction( $generate_id );

		/**
		 * Add do_action function.
		 */
		$do_action = new TwigFunction( 'do_action', 'do_action' );
		$this->twig->addFunction( $do_action );
	}

	/**
	 * Initialize additional twig filters
	 */
	public function initialize_twig_filters(): void {
		/**
		 * Add the WordPress apply_filters filter.
		 */
		$apply_filter = new TwigFilter(
			'apply_filters',
			function ( $text, $filter ) {
				return apply_filters( $filter, $text );
			}
		);
		$this->twig->addFilter( $apply_filter );

		/**
		 * Add the WordPress sanitize_title filter.
		 */
		$slugify = new TwigFilter( 'slugify', 'sanitize_title' );
		$this->twig->addFilter( $slugify );

		/**
		 * Add the WordPress wp_kses_post function filter.
		 *
		 * @link https://developer.wordpress.org/reference/functions/wp_kses_post/
		 */
		$wp_kses_post = new TwigFilter(
			'wp_kses_post',
			function ( $content ) {
				return wp_kses_post( $content );
			},
			[ 'is_safe' => [ 'html' ] ]
		);
		$this->twig->addFilter( $wp_kses_post );

		$number_format = new TwigFilter(
			'number_format_i18n',
			function ( $number ) {
				return number_format_i18n( $number );
			}
		);
		$this->twig->addFilter( $number_format );
	}

	/**
	 * Create the twig cache directory
	 */
	public function init(): void {
		$logger = $this->logger;

		if ( wp_mkdir_p( self::CACHE_DIR ) ) {
			$logger->info( 'Twig cache directory created successfully.', [ 'location' => self::CACHE_DIR ] );
			$this->clear_cache();

			return;
		}

		$logger->error( 'Unable to create Kudos Donations Twig cache directory', [ 'location' => self::CACHE_DIR ] );
	}

	/**
	 * Clears the twig cache
	 */
	public function clear_cache(): bool {
		$di      = new RecursiveDirectoryIterator( self::CACHE_DIR, FilesystemIterator::SKIP_DOTS );
		$ri      = new RecursiveIteratorIterator( $di, RecursiveIteratorIterator::CHILD_FIRST );
		$files   = 0;
		$folders = 0;
		foreach ( $ri as $file ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions
			$file->isDir() ? $files++ && rmdir( $file ) : $folders++ && unlink( $file );
		}
		$this->logger->debug(
			'Twig cache cleared.',
			[
				'files'   => $files,
				'folders' => $folders,
			]
		);

		return true;
	}

	/**
	 * Render the provided template.
	 *
	 * @param string $template Template file (.html.twig).
	 * @param array  $attributes Array to pass to template.
	 */
	public function render( string $template, array $attributes = [] ): ?string {
		try {
			return $this->twig->render( $template, $attributes );
		} catch ( Throwable $e ) {
			$this->logger->critical(
				$e->getMessage(),
				[
					'location' => $e->getFile(),
					'line'     => $e->getLine(),
				]
			);

			return null;
		}
	}
}
