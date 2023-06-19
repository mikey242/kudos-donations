<?php

namespace IseardMedia\Kudos\Service;

use FilesystemIterator;
use IseardMedia\Kudos\Helpers\Assets;
use IseardMedia\Kudos\Helpers\Utils;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigService
{
    public const CACHE_DIR = KUDOS_STORAGE_DIR . 'twig/cache/';
    /**
     * Directories where templates are stored.
     *
     * @var array
     */
    public $template_paths;
    /**
     * Twig environment.
     *
     * @var Environment
     */
    private $twig;
    /**
     * Twig options
     *
     * @var array
     */
    private $options;
    /**
     * @var \IseardMedia\Kudos\Service\LoggerService
     */
    private $logger;
    /**
     * @var \Twig\Loader\FilesystemLoader
     */
    private $loader;

    /**
     * Twig constructor
     *
     * @param \IseardMedia\Kudos\Service\LoggerService $logger_service
     */
    public function __construct(LoggerService $logger_service)
    {
        $this->logger           = $logger_service;
        $this->template_paths   = [KUDOS_PLUGIN_DIR . '/templates/'];
        $this->options['cache'] = KUDOS_DEBUG ? false : self::CACHE_DIR;
        $this->options['debug'] = KUDOS_DEBUG;
        $this->loader           = new FilesystemLoader();
        $this->initialize_twig();
    }

    /**
     * Initialize environment and loaders.
     */
    public function initialize_twig()
    {
        $paths  = apply_filters('kudos_twig_template_paths', $this->template_paths);
        $loader = $this->loader;

        foreach ($paths as $namespace => $path) {
            $this->add_path($path, $namespace);
        }

        $this->twig = new Environment($loader, $this->options);

        $this->initialize_twig_extensions();
        $this->initialize_twig_functions();
        $this->initialize_twig_filters();
    }

    public function add_path(string $path, $namespace = null)
    {
        $loader = $this->loader;
        try {
            if (is_string($namespace)) {
                $loader->setPaths($path, $namespace);
            } else {
                $loader->addPath($path);
            }
        } catch (LoaderError $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Initialize additional twig extensions
     */
    public function initialize_twig_extensions()
    {
        if (KUDOS_DEBUG) {
            $this->twig->addExtension(new DebugExtension());
        }
    }

    /**
     * Initialize additional twig functions
     *
     * @source https://wordpress.stackexchange.com/questions/287988/use-str-to-translate-strings-symfony-twig
     */
    public function initialize_twig_functions()
    {
        /**
         * Add gettext __ function.
         */
        $get_text = new TwigFunction('__', '__');
        $this->twig->addFunction($get_text);

        /**
         * Add gettext _n function.
         */
        $get_text = new TwigFunction('_n', '_n');
        $this->twig->addFunction($get_text);

        /**
         * Add get_option function.
         */
        $get_option = new TwigFunction('get_option', 'get_option');
        $this->twig->addFunction($get_option);

        /**
         * Add get_asset function.
         */
        $get_asset = new TwigFunction('get_asset', [Assets::class, 'get_style']);
        $this->twig->addFunction($get_asset);

        /**
         * Add generate_id function.
         */
        $generate_id = new TwigFunction('generate_id', [Utils::class, 'generate_id']);
        $this->twig->addFunction($generate_id);

        /**
         * Add do_action function.
         */
        $do_action = new TwigFunction('do_action', 'do_action');
        $this->twig->addFunction($do_action);
    }

    /**
     * Initialize additional twig filters
     */
    public function initialize_twig_filters()
    {
        /**
         * Add the WordPress apply_filters filter.
         */
        $apply_filter = new TwigFilter('apply_filters', function ($string, $filter) {
            return apply_filters($filter, $string);
        });
        $this->twig->addFilter($apply_filter);

        /**
         * Add the WordPress sanitize_title filter.
         */
        $slugify = new TwigFilter('slugify', 'sanitize_title');
        $this->twig->addFilter($slugify);

        /**
         * Add the WordPress wp_kses_post function filter.
         * @link https://developer.wordpress.org/reference/functions/wp_kses_post/
         */
        $wp_kses_post = new TwigFilter('wp_kses_post', function ($string) {
            return wp_kses_post($string);
        }, ['is_safe' => ['html']]);
        $this->twig->addFilter($wp_kses_post);

        $number_format = new TwigFilter('number_format_i18n', function ($number) {
            return number_format_i18n($number);
        });
        $this->twig->addFilter($number_format);
    }

    /**
     * Create the twig cache directory
     */
    public function init()
    {
        $logger = $this->logger;

        if (wp_mkdir_p(self::CACHE_DIR)) {
            $logger->info('Twig cache directory created successfully.', ['location' => self::CACHE_DIR]);
            $this->clearCache();

            return;
        }

        $logger->error('Unable to create Kudos Donations Twig cache directory', ['location' => self::CACHE_DIR]);
    }

    /**
     * Clears the twig cache
     *
     * @return bool
     */
    public function clearCache(): bool
    {
        $di      = new RecursiveDirectoryIterator(self::CACHE_DIR, FilesystemIterator::SKIP_DOTS);
        $ri      = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
        $files   = 0;
        $folders = 0;
        foreach ($ri as $file) {
            $file->isDir() ? $files++ && rmdir($file) : $folders++ && unlink($file);
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
     * @param array $array Array to pass to template.
     *
     * @return string|bool
     */
    public function render(string $template, array $array = [])
    {
        try {
            return $this->twig->render($template, $array);
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage(), ['location' => $e->getFile(), 'line' => $e->getLine()]);

            return false;
        }
    }
}
