<?php

namespace Kudos;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.linkedin.com/in/michael-iseard/
 * @since      1.0.0
 *
 * @package    Kudos-Donations
 * @subpackage Kudos/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Kudos-Donations
 * @subpackage Kudos/includes
 * @author     Michael Iseard <michael@iseard.media>
 */
class Kudos_Donations {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Kudos_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'KUDOS_VERSION' ) ) {
			$this->version = KUDOS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'kudos-donations';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once KUDOS_DIR . 'includes/kudos-helpers.php';
		require_once KUDOS_DIR . 'includes/class-kudos-logger.php';
		require_once KUDOS_DIR . 'includes/class-kudos-loader.php';
		require_once KUDOS_DIR . 'includes/class-kudos-i18n.php';
		require_once KUDOS_DIR . 'includes/class-transaction.php';
		require_once KUDOS_DIR . 'includes/class-transactions-table.php';
		require_once KUDOS_DIR . 'includes/class-kudos-carbon.php';
		require_once KUDOS_DIR . 'admin/class-kudos-admin.php';
		require_once KUDOS_DIR . 'public/class-kudos-public.php';
		require_once KUDOS_DIR . 'includes/class-kudos-mollie.php';
		require_once KUDOS_DIR . 'includes/class-kudos-twig.php';
		require_once KUDOS_DIR . 'public/class-kudos-button.php';
		require_once KUDOS_DIR . 'public/class-kudos-modal.php';

		$this->loader = new Kudos_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Kudos_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Kudos_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Kudos_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action('admin_menu', $plugin_admin, 'create_transaction_page', 11);
		$this->loader->add_action('wp_ajax_check_mollie_connection', $plugin_admin, 'check_mollie_connection');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Kudos_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'enqueue_block_assets', $plugin_public, 'enqueue_block_assets' );
		$this->loader->add_action('wp_ajax_nopriv_create_payment', $plugin_public, 'create_payment');
		$this->loader->add_action('wp_ajax_create_payment', $plugin_public, 'create_payment');
		$this->loader->add_action('wp_ajax_nopriv_check_transaction', $plugin_public, 'check_transaction');
		$this->loader->add_action('wp_ajax_check_transaction', $plugin_public, 'check_transaction');
		$this->loader->add_action('rest_api_init', $plugin_public, 'register_webhook');
		$this->loader->add_action('init', $plugin_public, 'register_shortcodes');
		$this->loader->add_action('wp_footer', $plugin_public, 'place_payment_modal', 1000);

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Kudos_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
