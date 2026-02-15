<?php
/**
 * TwigService.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Container\ActivationAwareInterface;
use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Helper\Utils;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigService implements ActivationAwareInterface, LoggerAwareInterface {

	use LoggerAwareTrait;

	public const CACHE_DIR = KUDOS_CACHE_DIR . 'twig/';
	private Environment $twig;
	private array $options = [];

	/**
	 * Twig constructor
	 */
	public function __construct() {
		$this->options['cache'] = self::CACHE_DIR;
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
	 * Create the twig cache directory
	 */
	public function on_plugin_activation(): void {
		if ( wp_mkdir_p( self::CACHE_DIR ) ) {
			$this->logger->info( 'Twig cache directory created successfully.', [ 'location' => self::CACHE_DIR ] );
			return;
		}

		$this->logger->error( 'Unable to create Kudos Donations Twig cache directory', [ 'location' => self::CACHE_DIR ] );
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

		/**
		 * Add get_bloginfo function.
		 */
		$get_bloginfo = new TwigFunction( 'get_bloginfo', 'get_bloginfo' );
		$this->twig->addFunction( $get_bloginfo );
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
			function ( string $text, callable $filter ): string {
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
			function ( float $number ): string {
				return number_format_i18n( $number );
			}
		);
		$this->twig->addFilter( $number_format );
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
