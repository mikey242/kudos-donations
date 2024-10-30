<?php
/**
 * Kudos Donations main plugin file.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

namespace Kudos;

use DI\Container;
use Kudos\Controller\Admin;
use Kudos\Controller\Front;
use Kudos\Service\ActivatorService;
use Kudos\Service\PaymentService;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.linkedin.com/in/michael-iseard/
 */

/**
 * The core plugin class.
 */
class KudosDonations {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * The container property.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @param Container $container The container.
	 * @param string    $version The plugin version number.
	 * @param string    $plugin_name The plugin name.
	 */
	public function __construct( Container $container, string $version, string $plugin_name ) {

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
	 */
	private function define_admin_hooks() {

		/** @var Admin $plugin_admin */
		$plugin_admin = $this->container->get( 'Admin' );

		add_action( 'admin_menu', [ $plugin_admin, 'add_menu_pages' ] );
		add_action( 'admin_init', [ $plugin_admin, 'admin_actions' ] );
		add_action( 'rest_api_init', [ $plugin_admin, 'register_settings' ] );
		add_action( 'admin_init', [ $plugin_admin, 'register_settings' ] );
		add_action( 'kudos_remove_secret_action', [ $plugin_admin, 'remove_secret_action' ], 10, 2 );
		add_action( 'kudos_check_log', [ $plugin_admin, 'truncate_log' ] );
		add_action( 'enqueue_block_editor_assets', [ $plugin_admin, 'register_block_editor_assets' ] );
		add_action( 'in_plugin_update_message-kudos-donations/kudos-donations.php', [ $plugin_admin, 'update_message' ], 10, 2 );
		add_action( 'admin_init', [ $plugin_admin, 'show_notices' ], 10, 2 );
	}

	/**
	 * Register all the hooks related to the public-facing functionality
	 * of the plugin.
	 */
	private function define_public_hooks() {

		/** @var Front $plugin_public */
		$plugin_public = $this->container->get( 'Front' );

		add_action( 'wp_enqueue_scripts', [ $plugin_public, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $plugin_public, 'register_styles' ] );
		add_action( 'enqueue_block_assets', [ $plugin_public, 'register_root_styles' ] ); // Used by front and admin.
		add_action( 'init', [ $plugin_public, 'register_kudos' ] );
		add_action( 'wp_footer', [ $plugin_public, 'handle_query_variables' ], 1000 );
	}

	/**
	 * Define mollie related hooks.
	 */
	private function define_payment_hooks() {

		/** @var PaymentService $payment_service */
		$payment_service = $this->container->get( 'PaymentService' );

		add_action( 'kudos_mollie_transaction_paid', [ $payment_service, 'schedule_process_transaction' ] );
		add_action( 'kudos_process_mollie_transaction', [ $payment_service, 'process_transaction' ] );
	}

	/**
	 * Checks plugin version stored in database and runs activation
	 * method if different.
	 */
	public function version_check() {

		$db_version = get_option( '_kudos_donations_version' );

		if ( KUDOS_VERSION !== $db_version ) {
			/** @var ActivatorService $activator */
			$activator = $this->container->get( 'ActivatorService' );
			$activator->activate( $db_version );
		}
	}
}
