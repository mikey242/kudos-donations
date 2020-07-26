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

		if(get_option('_kudos_action_scheduler')) {
			require_once KUDOS_DIR . '/libraries/action-scheduler/action-scheduler.php';
		}

		require_once KUDOS_DIR . 'includes/kudos-helpers.php';
		require_once KUDOS_DIR . 'includes/class-kudos-logger.php';
		require_once KUDOS_DIR . 'includes/class-kudos-loader.php';
		require_once KUDOS_DIR . 'includes/class-kudos-i18n.php';
		require_once KUDOS_DIR . 'includes/traits/class-database-trait.php';
		require_once KUDOS_DIR . 'includes/traits/class-table-trait.php';
		require_once KUDOS_DIR . 'includes/class-kudos-transaction.php';
		require_once KUDOS_DIR . 'includes/class-kudos-donor.php';
		require_once KUDOS_DIR . 'includes/class-kudos-subscription.php';
		require_once KUDOS_DIR . 'admin/class-transactions-table.php';
		require_once KUDOS_DIR . 'admin/class-subscriptions-table.php';
		require_once KUDOS_DIR . 'admin/class-donors-table.php';
		require_once KUDOS_DIR . 'admin/class-kudos-admin.php';
		require_once KUDOS_DIR . 'public/class-kudos-public.php';
		require_once KUDOS_DIR . 'includes/class-kudos-mollie.php';
		require_once KUDOS_DIR . 'includes/class-kudos-mailer.php';
		require_once KUDOS_DIR . 'includes/class-kudos-twig.php';
		require_once KUDOS_DIR . 'includes/class-kudos-invoice.php';
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

		$this->loader->add_action('plugins_loaded', $this, 'version_check');
		$this->loader->add_action('admin_menu', $plugin_admin, 'kudos_add_menu_pages', 11);
		$this->loader->add_action('admin_init', $plugin_admin, 'admin_actions');
		$this->loader->add_action('init', $plugin_admin, 'register_settings');
		$this->loader->add_action('rest_api_init', $plugin_admin, 'register_routes');
		$this->loader->add_action('admin_post_cancel_subscription', $plugin_admin, 'cancel_subscription');

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
		$this->loader->add_action('rest_api_init', $plugin_public, 'register_routes');
		$this->loader->add_action('init', $plugin_public, 'register_kudos');
		$this->loader->add_action('wp_footer', $plugin_public, 'place_message_modal', 1000);
		$this->loader->add_action('wp_footer', $plugin_public, 'get_cancel_vars', 1000);
		$this->loader->add_action('query_vars', $plugin_public, 'register_vars');
		$this->loader->add_action( 'kudos_process_transaction_action', $plugin_public, 'process_transaction', 10, 1 );

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

	/**
	 * Checks plugin version stored in database and runs activation
	 * hook if different.
	 *
	 * @since 1.0.2
	 */
	public function version_check() {

		$db_version = get_option('kudos_donations_version');

		if($db_version !== $this->get_version()) {
			require_once KUDOS_DIR . 'includes/class-kudos-activator.php';
			Kudos_Activator::activate();
		}
	}

}
