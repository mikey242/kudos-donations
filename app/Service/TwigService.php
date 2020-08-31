<?php

namespace Kudos\Service;

use Kudos\Helpers\Utils;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigService {

	const CACHE_DIR = KUDOS_STORAGE_DIR . '/twig/cache/';

	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var LoggerService
	 */
	private $logger;

	/**
	 * Twig constructor
	 *
	 * @param string $templates_dir
	 *
	 * @since    1.0.0
	 */
	public function __construct($templates_dir=KUDOS_PLUGIN_DIR . '/templates/') {

		$loader = new FilesystemLoader($templates_dir);
		$cache = (KUDOS_DEBUG ? false : self::CACHE_DIR);
		$this->twig = new Environment($loader, [
			'cache' => $cache,
		]);
		$this->logger = new LoggerService();
		$this->initialize_twig_functions();
		$this->initialize_twig_filters();

	}

	/**
	 * Create the twig cache directory
	 *
	 * @since    2.0.0
	 */
	public static function init() {

		wp_mkdir_p(self::CACHE_DIR);

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
		$apply_filter = new TwigFilter('apply_filters', 'apply_filters');
		$this->twig->addFilter($apply_filter);

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
		$color_luminance = new TwigFunction('color_luminance', [Utils::class, 'color_luminance']);
		$this->twig->addFunction($color_luminance);

		/**
		 * Add get_asset function.
		 */
		$get_asset = new TwigFunction('get_asset', [Utils::class, 'get_asset_url']);
		$this->twig->addFunction($get_asset);
	}

	/**
	 * Render the provided template
	 *
	 * @param string $template
	 * @param array $array
	 *
	 * @return bool
	 * @since    1.0.0
	 */
	public function render(string $template, $array=[]) {

		try {
			return $this->twig->render( $template, $array );
		} catch (Throwable $e ) {
			$this->logger->critical($e->getMessage(), [$template]);
			return false;
		}

	}
}