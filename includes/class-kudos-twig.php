<?php

namespace Kudos;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use Throwable;

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

	/**
	 * Twig constructor
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$path = KUDOS_DIR . 'templates/';
		$loader = new FilesystemLoader($path);
		$this->twig = new Environment($loader);
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
		 * Add gettext __ functions to twig functions.
		 */
		$function = new TwigFunction('__', '__');
		$this->twig->addFunction($function);
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
			$this->logger->log($e->getMessage(), 'CRITICAL');
			return false;
		}
	}
}