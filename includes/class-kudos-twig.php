<?php

namespace Kudos;

use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class Kudos_Twig
{
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var Kudos_Logger
	 */
	private $logger;

	const TEMPLATES_DIR = KUDOS_DIR . 'templates/';
	const CACHE_DIR = KUDOS_DIR . 'cache/';

	/**
	 * Twig constructor
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$loader = new FilesystemLoader(self::TEMPLATES_DIR);
		$cache = (KUDOS_DEBUG ? false : self::CACHE_DIR);
		$this->twig = new Environment($loader, [
			'cache' => $cache,
		]);
		$this->logger = new Kudos_Logger();
		$this->initialize_twig_functions();
	}

	/**
	 * Initialize additional twig functions
	 *
	 * @since    1.0.0
	 * @source https://wordpress.stackexchange.com/questions/287988/use-str-to-translate-strings-symfony-twig
	 */
	public function initialize_twig_functions() {

		/**
		 * Add gettext __ functions.
		 */
		$get_text = new TwigFunction('__', '__');
		$this->twig->addFunction($get_text);

		/**
		 * Add get_option function.
		 */
		$get_option = new TwigFunction('get_option', 'get_option');
		$this->twig->addFunction($get_option);

		/**
		 * Add color_luminance helper function.
		 */
		$color_luminance = new TwigFunction('color_luminance', 'color_luminance');
		$this->twig->addFunction($color_luminance);

		/**
		 * Add get_asset function.
		 */
		$get_asset = new TwigFunction('get_asset', 'get_asset_url');
		$this->twig->addFunction($get_asset);
	}

	/**
	 * Render the provided template
	 *
	 * @since    1.0.0
	 * @param $template
	 * @param array $array
	 *
	 * @return bool
	 */
	public function render($template, $array=[]) {
		try {
			return $this->twig->render( $template, $array );
		} catch (Throwable $e ) {
			$this->logger->critical($e->getMessage());
			return false;
		}
	}
}