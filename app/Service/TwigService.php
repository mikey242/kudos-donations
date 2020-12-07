<?php

namespace Kudos\Service;

use FilesystemIterator;
use Kudos\Helpers\Utils;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use Twig\Environment;
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
	private $templates_dir;

	/**
	 * Twig options
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Twig constructor
	 *
	 * @param array $templates_dir Templates directory array.
	 * @param array $options Twig options.
	 *
	 * @since    1.0.0
	 */
	public function __construct( array $templates_dir = [], array $options = [] ) {

		parent::__construct();

		$this->templates_dir    = $templates_dir;
		$this->templates_dir[]  = KUDOS_PLUGIN_DIR . '/templates/';
		$this->options          = $options;
		$this->options['cache'] = KUDOS_DEBUG ? false : self::CACHE_DIR;
		$this->initialize_twig();
		$this->initialize_twig_functions();
		$this->initialize_twig_filters();

	}

	/**
	 * Initialize environment and loaders
	 */
	public function initialize_twig() {

		$loader     = new FilesystemLoader( $this->templates_dir );
		$this->twig = new Environment( $loader, $this->options );

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

	}

	/**
	 * Create the twig cache directory
	 *
	 * @since    2.0.0
	 */
	public static function initCache() {

		$logger = LoggerService::factory();

		if ( wp_mkdir_p( self::CACHE_DIR ) ) {
			$logger->info( 'Twig cache directory created successfully' );

			return;
		}

		$logger->error( 'Unable to create Kudos Donations Twig cache directory', [ self::CACHE_DIR ] );

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
			$this->logger->critical( $e->getMessage(), [ $template ] );

			return false;
		}

	}

	/**
	 * Clears the twig cache
	 *
	 * @return bool
	 */
	public function clearCache() {

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
}
