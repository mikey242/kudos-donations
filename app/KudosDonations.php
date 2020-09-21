<?php

namespace Kudos;

use Kudos\Admin\Admin;
use Kudos\Front\Front;
use Kudos\Helpers\Settings;
use Kudos\Service\ActivatorService;
use Kudos\Service\I18nService;
use Kudos\Service\LoaderService;

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
class KudosDonations {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      LoaderService $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
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
		$this->loader      = new LoaderService();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		if ( Settings::get_setting( 'action_scheduler' ) ) {
			require_once( KUDOS_PLUGIN_DIR . '/libraries/action-scheduler/action-scheduler.php' );
		}

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

		$plugin_i18n = new I18nService();

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

		$plugin_admin = new Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'plugins_loaded', $this, 'version_check' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'kudos_add_menu_pages', 11 );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_actions' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		$this->loader->add_action( 'rest_api_init', $plugin_admin, 'register_routes' );
		$this->loader->add_action( 'rest_api_init', $plugin_admin, 'register_settings' );
		$this->loader->add_action( 'wp_verify_nonce_failed', $plugin_admin, 'nonce_fail', 10, 2 );

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {

		return $this->plugin_name;

	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {

		return $this->version;

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Front( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles', 9999 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'enqueue_block_assets', $plugin_public, 'enqueue_block_assets' );
		$this->loader->add_action( 'wp_ajax_nopriv_submit_payment', $plugin_public, 'submit_payment' );
		$this->loader->add_action( 'wp_ajax_submit_payment', $plugin_public, 'submit_payment' );
		$this->loader->add_action( 'init', $plugin_public, 'register_kudos' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'handle_query_variables', 1000 );
		$this->loader->add_action( 'query_vars', $plugin_public, 'register_vars' );
		$this->loader->add_action( 'kudos_process_paid_transaction', $plugin_public, 'process_transaction', 10, 1 );
		$this->loader->add_action( 'kudos_remove_secret_action', $plugin_public, 'remove_donor_secret', 10, 1 );

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
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return LoaderService Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {

		return $this->loader;

	}

	/**
	 * Checks plugin version stored in database and runs activation
	 * hook if different.
	 *
	 * @since 1.0.2
	 */
	public function version_check() {

		$db_version = get_option( '_kudos_donations_version' );

		if ( $db_version !== $this->get_version() ) {
			ActivatorService::activate();
		}
	}

}
