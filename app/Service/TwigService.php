<?php

namespace Kudos\Service;

use FilesystemIterator;
use Kudos\Helpers\Utils;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigService extends AbstractService {

	const CACHE_DIR = KUDOS_STORAGE_DIR . 'twig/cache/';

	/**
	 * Twig environment.
	 *
	 * @var Environment
	 */
	private $twig;

	/**
	 * Directories where templates are stored.
	 *
	 * @var array
	 */
	private $template_paths;

	/**
	 * Twig options
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Twig constructor
	 *
	 * @param array $template_paths Templates directory array.
	 * @param array $options Twig options.
	 *
	 * @since    1.0.0
	 */
	public function __construct( array $template_paths = [], array $options = [] ) {

		parent::__construct();

		$this->template_paths[]        = KUDOS_PLUGIN_DIR . '/templates/'; // Always add main template directory to paths.
		$this->template_paths['extra'] = $template_paths; // Add extra under '@extra' namespace.
		$this->options                 = $options;
		$this->options['cache']        = KUDOS_DEBUG ? false : self::CACHE_DIR;
		$this->options['debug']        = KUDOS_DEBUG;
		$this->initialize_twig();
		$this->initialize_twig_extensions();
		$this->initialize_twig_functions();
		$this->initialize_twig_filters();

	}

	/**
	 * Initialize environment and loaders
	 */
	public function initialize_twig() {

		$loader = new FilesystemLoader();
		foreach ( $this->template_paths as $namespace => $path ) {
			if ( is_string( $namespace ) ) {
				$loader->setPaths( $path, $namespace );
			} else {
				try {
					$loader->addPath( $path );
				} catch (LoaderError $e) {
					$this->logger->error($e->getMessage());
				}
			}
		}
		$this->twig = new Environment( $loader, $this->options );

	}

	/**
	 * Initialize additional twig extensions
	 */
	public function initialize_twig_extensions() {

		if ( KUDOS_DEBUG ) {
			$this->twig->addExtension( new DebugExtension() );
		}
	}

	/**
	 * Initialize additional twig functions
	 *
	 * @since    1.0.0
	 * @source https://wordpress.stackexchange.com/questions/287988/use-str-to-translate-strings-symfony-twig
	 */
	public function initialize_twig_functions() {

		/**
		 * Add gettext __ function.
		 */
		$get_text = new TwigFunction( '__', '__' );
		$this->twig->addFunction( $get_text );

		/**
		 * Add gettext _n function.
		 */
		$get_text = new TwigFunction( '_n', '_n' );
		$this->twig->addFunction( $get_text );

		/**
		 * Add get_option function.
		 */
		$get_option = new TwigFunction( 'get_option', 'get_option' );
		$this->twig->addFunction( $get_option );

		/**
		 * Add color_luminance helper function.
		 */
		$color_luminance = new TwigFunction( 'color_luminance', [ Utils::class, 'color_luminance' ] );
		$this->twig->addFunction( $color_luminance );

		/**
		 * Add get_asset function.
		 */
		$get_asset = new TwigFunction( 'get_asset', [ Utils::class, 'get_asset_url' ] );
		$this->twig->addFunction( $get_asset );

		/**
		 * Add do_action function.
		 */
		$do_action = new TwigFunction( 'do_action', 'do_action' );
		$this->twig->addFunction( $do_action );
	}

	/**
	 * Initialize additional twig filters
	 *
	 * @since 2.0.0
	 */
	public function initialize_twig_filters() {

		/**
		 * Add the WordPress apply_filters filter.
		 */
		$apply_filter = new TwigFilter( 'apply_filters', 'apply_filters' );
		$this->twig->addFilter( $apply_filter );

		/**
		 * Add the WordPress sanitize_title filter.
		 */
		$slugify = new TwigFilter( 'slugify', 'sanitize_title' );
		$this->twig->addFilter( $slugify );

		/**
		 * Add the WordPress wp_kses_post function filter.
		 * @link https://developer.wordpress.org/reference/functions/wp_kses_post/
		 */
		$wp_kses_post = new TwigFilter( 'wp_kses_post', function ( $string ) {
			return wp_kses_post( $string );

		}, [ 'is_safe' => [ 'html' ] ] );
		$this->twig->addFilter( $wp_kses_post );

	}

	/**
	 * Create the twig cache directory
	 *
	 * @since    2.0.0
	 */
	public function init() {

		$logger = $this->logger;

		if ( wp_mkdir_p( self::CACHE_DIR ) ) {
			$logger->info( 'Twig cache directory created successfully', [ 'location' => self::CACHE_DIR ] );
			$this->clearCache();

			return;
		}

		$logger->error( 'Unable to create Kudos Donations Twig cache directory', [ 'location' => self::CACHE_DIR ] );

	}

	/**
	 * Clears the twig cache
	 *
	 * @return bool
	 */
	public function clearCache(): bool {

		$di      = new RecursiveDirectoryIterator( self::CACHE_DIR, FilesystemIterator::SKIP_DOTS );
		$ri      = new RecursiveIteratorIterator( $di, RecursiveIteratorIterator::CHILD_FIRST );
		$files   = 0;
		$folders = 0;
		foreach ( $ri as $file ) {
			$file->isDir() ? $files ++ && rmdir( $file ) : $folders ++ && unlink( $file );
		}
		$this->logger->debug(
			'Twig cache cleared',
			[
				'files'   => $files,
				'folders' => $folders,
			]
		);

		return true;
	}

	/**
	 * Render the provided template
	 *
	 * @param string $template Template file (.html.twig).
	 * @param array $array Array to pass to template.
	 *
	 * @return string|bool
	 * @since    1.0.0
	 */
	public function render( string $template, $array = [] ) {

		try {
			return $this->twig->render( $template, $array );
		} catch ( Throwable $e ) {
			$this->logger->critical( $e->getMessage(), [ 'template' => $template, 'line' => $e->getLine() ] );

			return false;
		}

	}
}
