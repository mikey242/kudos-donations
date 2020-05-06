<?php

namespace Kudos\Twig;

use Kudos\Logger\Kudos_Logger;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Kudos_Twig
{

	public $twig;
	private $logger;

	/**
	 * Twig constructor
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$loader = new FilesystemLoader(KUDOS_DIR . 'templates/');
		$this->twig = new Environment($loader);
		$this->logger = new Kudos_Logger();
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