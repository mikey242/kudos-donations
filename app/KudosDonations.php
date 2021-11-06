<?php

namespace Kudos;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.linkedin.com/in/michael-iseard/
 *
 * @package    Kudos-Donations
 * @subpackage Kudos/includes
 */

/**
 * The core plugin class.
 *
 * @package    Kudos-Donations
 * @subpackage Kudos/includes
 * @author     Michael Iseard <michael@iseard.media>
 */
class KudosDonations {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * The container property.
	 *
	 * @var \DI\Container
	 */
	protected $container;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 */
	public function __construct( $container, $version, $plugin_name ) {

		$this->container   = $container;
		$this->version     = $version;
		$this->plugin_name = $plugin_name;

	}

	/**
	 * Run the loader to execute all the hooks with WordPress.
	 */
	public function run() {

		$this->define_rest_hooks();
		$this->define_admin_hooks();
		$this->define_payment_hooks();
		$this->define_public_hooks();

		add_action( 'plugins_loaded', [ $this, 'version_check' ] );

	}

	/**
	 * Initialize rest service and register routes.
	 */
	private function define_rest_hooks() {

		$rest_routes = $this->container->get( 'RestRoutes' );

		add_action( 'rest_api_init', [ $rest_routes, 'register_all' ] );

	}

	/**
	 * Register all the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = $this->container->get( 'Admin' );

		add_action( 'admin_menu', [ $plugin_admin, 'add_menu_pages' ] );
		add_action( 'admin_init', [ $plugin_admin, 'admin_actions' ] );
		add_action( 'rest_api_init', [ $plugin_admin, 'register_settings' ] );
		add_action( 'admin_init', [ $plugin_admin, 'register_settings' ] );
		add_action( 'kudos_remove_secret_action', [ $plugin_admin, 'remove_secret_action' ], 10, 2 );
		add_action( 'kudos_check_log', [ $plugin_admin, 'truncate_log' ] );
		add_action( 'enqueue_block_editor_assets', [ $plugin_admin, 'register_block_editor_assets' ] );

	}

	/**
	 * Register all the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = $this->container->get( 'Front' );

		add_action( 'wp_enqueue_scripts', [ $plugin_public, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $plugin_public, 'register_styles' ] );
		add_action( 'enqueue_block_assets', [ $plugin_public, 'register_root_styles' ] ); // Used by front and admin
		add_action( 'init', [ $plugin_public, 'register_kudos' ] );
		add_action( 'wp_footer', [ $plugin_public, 'handle_query_variables' ], 1000 );

	}

	/**
	 * Define mollie related hooks.
	 */
	private function define_payment_hooks() {

		$payment_service = $this->container->get( 'PaymentService' );

		add_action( 'kudos_mollie_transaction_paid', [ $payment_service, 'schedule_process_transaction' ] );
		add_action( 'kudos_process_mollie_transaction', [ $payment_service, 'process_transaction' ] );

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name(): string {

		return $this->plugin_name;

	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 */
	public function get_version(): string {

		return $this->version;

	}

	/**
	 * Checks plugin version stored in database and runs activation
	 * method if different.
	 */
	public function version_check() {

		$db_version = get_option( '_kudos_donations_version' );

		if ( $db_version !== KUDOS_VERSION ) {
			$this->container->get( 'ActivatorService' )
			                ->activate( $db_version );
		}
	}

}
