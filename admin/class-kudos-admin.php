<?php

namespace Kudos;

use Kudos\Table\Donors;
use Kudos\Table\Subscriptions;
use Kudos\Table\Transactions;
use WP_REST_Server;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/michael-iseard/
 * @since      1.0.0
 *
 * @package    Kudos-Donations
 * @subpackage Kudos/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Kudos-Donations
 * @subpackage Kudos/admin
 * @author     Michael Iseard <michael@iseard.media>
 */
class Kudos_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Registers REST routes
	 *
	 * @since   2.0.0
	 * @return void
	 */
	public function register_routes() {

		// Mollie API key check
		$mollie = new Kudos_Mollie();
		$mollie->register_api_key_check();

		// Test Email
        $mailer = new Kudos_Mailer();
        $mailer->register_send_test_email();

        // Diagnostics
		register_rest_route('kudos/v1', 'diagnostics', [
			'methods'   => WP_REST_Server::READABLE,
			'callback'  => [$this, 'diagnostics'],
		]);
	}

	/**
     * Create the Kudos Donations admin pages
     *
	 * @since   2.0.0
	 */
	public function kudos_add_menu_pages() {

	    add_menu_page(
	        __('Kudos', 'kudos-donations'),
            __('Kudos', 'kudos-donations'),
            'manage_options',
            'kudos-settings',
            false,
		    'data:image/svg+xml;base64,' . base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 555 449"><defs/><path fill="#f0f5fa99" d="M0-.003h130.458v448.355H.001zM489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/></svg>
            ')

        );

		$settings_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			__( 'Kudos Settings', 'kudos-donations' ),
			__( 'Settings', 'kudos-donations' ),
			'manage_options',
			'kudos-settings',
			function() {
				echo '<div id="kudos-settings"></div>';
            }
		);
		add_action( "admin_print_scripts-{$settings_page_hook_suffix}", [$this, 'kudos_settings_page_assets'] );

		$transactions_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf(__('%s Transactions', 'kudos-donations'), 'Kudos'),
			__('Transactions', 'kudos-donations'),
			'manage_options',
			'kudos-transactions',
			function () {
				require_once KUDOS_DIR . 'admin/partials/kudos-admin-transactions.php';
			}

		);
		add_action( "admin_print_scripts-{$transactions_page_hook_suffix}", [$this, 'kudos_transactions_page_assets'] );

		$subscriptions_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf(__('%s Subscriptions', 'kudos-donations'), 'Kudos'),
			__('Subscriptions', 'kudos-donations'),
			'manage_options',
			'kudos-subscriptions',
			function () {
				require_once KUDOS_DIR . 'admin/partials/kudos-admin-subscriptions.php';
			}

		);
		add_action( "admin_print_scripts-{$subscriptions_page_hook_suffix}", [$this, 'kudos_subscriptions_page_assets'] );

		$donors_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf(__('%s Donors', 'kudos-donations'), 'Kudos'),
			__('Donors', 'kudos-donations'),
			'manage_options',
			'kudos-donors',
			function () {
				require_once KUDOS_DIR . 'admin/partials/kudos-admin-donors.php';
			}

		);
		add_action( "admin_print_scripts-{$donors_page_hook_suffix}", [$this, 'kudos_donor_page_assets'] );

        // Add debug menu
        if(KUDOS_DEBUG) {
	        add_submenu_page(
		        'kudos-settings',
		        'Kudos Debug',
		        'Debug',
		        'manage_options',
		        'kudos-debug',
		        function () {
			        require_once KUDOS_DIR . 'admin/partials/kudos-admin-debug.php';
                }
	        );
        }

	}

	/**
     * Assets specific to the Kudos Settings page
     *
	 * @since   2.0.0
	 */
	public function kudos_settings_page_assets() {

	    $handle = $this->plugin_name . '-settings';

		wp_enqueue_script( $handle, get_asset_url('kudos-admin-settings.js'), [ 'wp-api', 'wp-i18n', 'wp-components', 'wp-element', 'wp-data' ], $this->version, true );
		wp_localize_script($handle, 'kudos', [
			'version' => KUDOS_VERSION,
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'checkApiUrl' => rest_url('kudos/v1/mollie/admin'),
			'sendTestUrl' => rest_url('kudos/v1/email/test'),
			'getDiagnosticsUrl' => rest_url('kudos/v1/diagnostics'),
			'ajaxurl' => admin_url('admin-ajax.php'),
		]);
		wp_set_script_translations( $handle, 'kudos-donations', KUDOS_DIR . 'languages');
		wp_enqueue_style( 'kudos-donations-admin-react', get_asset_url('kudos-admin-settings.css'), [ 'wp-components' ], $this->version,'all' );
	}

	/**
	 * Assets specific to the Kudos Table pages
     *
     * @since 2.0.0
	 */
	private function kudos_table_page_assets() {

	    $handle = $this->plugin_name . '-table';
		wp_enqueue_script( $handle, get_asset_url('kudos-admin-table.js'), [ 'jquery' ], $this->version, false );
        return $handle;
    }

	/**
	 * Assets specific to the Kudos Transactions page
	 *
	 * @since   2.0.0
	 */
	public function kudos_transactions_page_assets() {

		wp_enqueue_script( $this->plugin_name . '-transactions', get_asset_url('kudos-admin-transactions.js'), [ 'jquery' ], $this->version, false );

		// Load table assets
	    $tableHandle = $this->kudos_table_page_assets();
		wp_localize_script($tableHandle, 'kudos', [
			'confirmation' => __('Are you sure you want to delete this transaction?', 'kudos-donations'),
		]);
	}

	/**
	 * Assets specific to the Kudos Subscriptions page
	 *
	 * @since   2.0.0
	 */
	public function kudos_subscriptions_page_assets() {

		// Load table assets
		$tableHandle = $this->kudos_table_page_assets();
		wp_localize_script($tableHandle, 'kudos', [
			'confirmation' => __('Are you sure you want to cancel this subscription?', 'kudos-donations'),
		]);
	}

	/**
	 * Assets specific to the Kudos Donors page
	 *
	 * @since   2.0.0
	 */
	public function kudos_donor_page_assets() {

		// Load table assets
		$tableHandle = $this->kudos_table_page_assets();
		wp_localize_script($tableHandle, 'kudos', [
			'confirmation' => __('Are you sure you want to delete this donor?', 'kudos-donations'),
		]);
	}

	/**
	 * Actions triggered by request data in the admin.
     * Needs to be hooked to admin_init as it modifies headers.
     *
	 * @since    1.0.1
	 */
	public function admin_actions() {

	    if(isset($_REQUEST['export_transactions'])) {
	        $table = new Transactions();
	        $table->export();
	    }

		if(isset($_REQUEST['export_subscriptions'])) {
			$table = new Subscriptions();
			$table->export();
		}

		if(isset($_REQUEST['export_donors'])) {
			$table = new Donors();
			$table->export();
		}

		if(isset($_REQUEST['clear_log'])) {
		    $kudos_logger = new Kudos_Logger();
		    $kudos_logger->clear();
		}

		if(isset($_REQUEST['download_log'])) {
			$kudos_logger = new Kudos_Logger();
			$kudos_logger->download();
		}
	}

	/**
	 * Returns diagnostic information
	 *
	 * @since   2.0.0
	 * @return string
	 */
	public function diagnostics() {

	    $response = [
	        'phpVersion' => phpversion(),
            'mbstring' => extension_loaded('mbstring'),
            'invoiceWriteable'  => Kudos_Invoice::isWriteable(),
            'logWriteable'  => Kudos_Logger::isWriteable(),
            'permalinkStructure' => get_option( 'permalink_structure' )
        ];

		wp_send_json_success($response);
		wp_die();
	}

	public function cancel_subscription() {
	    if(!wp_verify_nonce($_REQUEST['_wpnonce'], 'cancel_subscription')) {
	        echo "Nope!";
	        die;
	    }
	    $kudos_mollie = new Kudos_Mollie();
	    $subscription = $kudos_mollie->cancel_subscription($_REQUEST['subscriptionId'], $_REQUEST['customerId']);
	    if($subscription) {
	        echo "Subscription canceled";
	    }
	}

	/**
     * Register the plugin settings
     *
	 * @since    1.0.1
	 */
	public function register_settings() {

		require_once KUDOS_DIR . 'admin/partials/kudos-settings.php';

	}

}
