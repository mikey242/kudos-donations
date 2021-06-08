<?php

namespace Kudos;

use Kudos\Admin\Admin;
use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Controller\Front;
use Kudos\Service\ActivatorService;
use Kudos\Service\PaymentService;
use Kudos\Service\RestRouteService;

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
 * @since      1.0.0
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
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 */
	public function __construct() {

		if ( defined( 'KUDOS_VERSION' ) ) {
			$this->version = KUDOS_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->plugin_name = 'kudos-donations';

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 */
	public function run() {

		$this->define_admin_hooks();
		$this->define_rest_hooks();
		$this->define_payment_hooks();
		$this->define_public_hooks();
		$this->define_entity_hooks();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Admin( $this->get_plugin_name(), $this->get_version() );

		add_action( 'plugins_loaded', [ $this, 'version_check' ] );
		add_action( 'admin_menu', [ $plugin_admin, 'kudos_add_menu_pages' ], 11 );
		add_action( 'admin_init', [ $plugin_admin, 'admin_actions' ] );
		add_action( 'admin_init', [ $plugin_admin, 'register_settings' ] );
		add_action( 'rest_api_init', [ $plugin_admin, 'register_settings' ] );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Front( $this->get_plugin_name(), $this->get_version() );

		add_action( 'wp_enqueue_scripts', [ $plugin_public, 'enqueue_scripts' ] );
		add_action( 'enqueue_block_assets', [ $plugin_public, 'enqueue_block_assets' ] );
		add_action( 'init', [ $plugin_public, 'register_kudos' ] );
		add_action( 'wp_footer', [ $plugin_public, 'handle_query_variables' ], 1000 );

	}

	/**
	 * Initialize rest service and register routes.
	 *
	 * @since 2.3.0
	 */
	private function define_rest_hooks() {

		add_action( 'rest_api_init', [ new RestRouteService(), 'register_all' ] );

	}

	/**
	 * Define mollie related hooks.
	 *
	 * @since 2.3.0
	 */
	private function define_payment_hooks() {

		add_action( 'kudos_mollie_transaction_paid', [ PaymentService::class, 'schedule_process_transaction' ] );
		add_action( 'kudos_process_mollie_transaction', [ PaymentService::class, 'process_transaction' ] );

	}

	/**
	 * Register all the entity related hooks.
	 *
	 * @since 2.0.5
	 */
	private function define_entity_hooks() {

		// Action triggered by Action Scheduler to remove the entity secret.
		add_action( TransactionEntity::get_table_name( false ) . '_remove_secret_action',
			[ TransactionEntity::class, 'remove_secret_action' ] );
		add_action( DonorEntity::get_table_name( false ) . '_remove_secret_action',
			[ DonorEntity::class, 'remove_secret_action' ] );
		add_action( SubscriptionEntity::get_table_name( false ) . '_remove_secret_action',
			[ SubscriptionEntity::class, 'remove_secret_action' ] );

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name(): string {

		return $this->plugin_name;

	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version(): string {

		return $this->version;

	}

	/**
	 * Checks plugin version stored in database and runs activation
	 * method if different.
	 *
	 * @since 1.0.2
	 */
	public function version_check() {

		$db_version = get_option( '_kudos_donations_version' );

		if ( $db_version !== $this->get_version() ) {
			ActivatorService::activate( $db_version );
		}
	}

}
