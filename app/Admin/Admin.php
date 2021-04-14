<?php

namespace Kudos\Admin;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\ActivatorService;
use Kudos\Service\AdminNotice;
use Kudos\Service\LoggerService;
use Kudos\Service\MapperService;
use Kudos\Service\RestRouteService;
use Kudos\Service\TwigService;

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
class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( string $plugin_name, string $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Create the Kudos Donations admin pages.
	 *
	 * @since   2.0.0
	 */
	public function kudos_add_menu_pages() {

		add_menu_page(
			__( 'Kudos', 'kudos-donations' ),
			__( 'Donations', 'kudos-donations' ),
			'manage_options',
			'kudos-settings',
			false,
			'data:image/svg+xml;base64,' . base64_encode(
				'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 555 449"><defs/><path fill="#f0f5fa99" d="M0-.003h130.458v448.355H.001zM489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/></svg>'
			)

		);

		$settings_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			__( 'Kudos Settings', 'kudos-donations' ),
			__( 'Settings', 'kudos-donations' ),
			'manage_options',
			'kudos-settings',
			function () {
				echo '<div id="kudos-settings"></div>';
			}
		);
		add_action( "admin_print_scripts-$settings_page_hook_suffix", [ $this, 'kudos_settings_page_assets' ] );

		$transactions_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf( __( '%s Transactions', 'kudos-donations' ), 'Kudos' ),
			__( 'Transactions', 'kudos-donations' ),
			'manage_options',
			'kudos-transactions',
			function () {
				require_once KUDOS_PLUGIN_DIR . '/app/Admin/partials/kudos-admin-transactions.php';
			}

		);
		add_action( "admin_print_scripts-$transactions_page_hook_suffix",
			[ $this, 'kudos_transactions_page_assets' ] );

		$subscriptions_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf( __( '%s Subscriptions', 'kudos-donations' ), 'Kudos' ),
			__( 'Subscriptions', 'kudos-donations' ),
			'manage_options',
			'kudos-subscriptions',
			function () {
				require_once KUDOS_PLUGIN_DIR . '/app/Admin/partials/kudos-admin-subscriptions.php';
			}

		);
		add_action( "admin_print_scripts-$subscriptions_page_hook_suffix",
			[ $this, 'kudos_subscriptions_page_assets' ] );

		$donors_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf( __( '%s Donors', 'kudos-donations' ), 'Kudos' ),
			__( 'Donors', 'kudos-donations' ),
			'manage_options',
			'kudos-donors',
			function () {
				require_once KUDOS_PLUGIN_DIR . '/app/Admin/partials/kudos-admin-donors.php';
			}

		);
		add_action( "admin_print_scripts-$donors_page_hook_suffix", [ $this, 'kudos_donor_page_assets' ] );

		$campaigns_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf( __( '%s Campaigns', 'kudos-donations' ), 'Kudos' ),
			__( 'Campaigns', 'kudos-donations' ),
			'manage_options',
			'kudos-campaigns',
			function () {
				require_once KUDOS_PLUGIN_DIR . '/app/Admin/partials/kudos-admin-campaigns.php';
			}

		);
		add_action( "admin_print_scripts-$campaigns_page_hook_suffix", [ $this, 'kudos_campaign_page_assets' ] );

		// Add debug menu.
		$debug_page_hook_suffix = add_submenu_page(
			KUDOS_DEBUG ? 'kudos-settings' : null,
			'Kudos Debug',
			'Debug',
			'manage_options',
			'kudos-debug',
			function () {
				require_once KUDOS_PLUGIN_DIR . '/app/Admin/partials/kudos-admin-debug.php';
			}
		);
		add_action( "admin_print_scripts-$debug_page_hook_suffix",
			function () {
				?>
				<script>
                    document.addEventListener("DOMContentLoaded", function () {
                        let buttons = document.querySelectorAll('button[type="submit"].confirm')
                        for (let i = 0; i < buttons.length; i++) {
                            buttons[i].addEventListener('click', function (e) {
                                if (!confirm('<?php _e( 'Are you sure?', 'kudos-donations' ) ?>')) {
                                    e.preventDefault()
                                }
                            })
                        }
                    })
				</script>
				<?php
			} );
	}

	/**
	 * Assets specific to the Kudos Settings page.
	 *
	 * @since   2.0.0
	 */
	public function kudos_settings_page_assets() {

		$handle = $this->plugin_name . '-settings';

		wp_enqueue_script(
			$handle,
			Utils::get_asset_url( 'kudos-admin-settings.js' ),
			[ 'wp-api', 'wp-i18n', 'wp-components', 'wp-element' ],
			$this->version,
			true
		);
		wp_localize_script(
			$handle,
			'kudos',
			[
				'version'     => KUDOS_VERSION,
				'checkApiUrl' => rest_url( RestRouteService::NAMESPACE . RestRouteService::PAYMENT_TEST ),
				'sendTestUrl' => rest_url( RestRouteService::NAMESPACE . RestRouteService::EMAIL_TEST ),
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
			]
		);
		wp_set_script_translations( $handle, 'kudos-donations', KUDOS_PLUGIN_DIR . '/languages' );
		wp_enqueue_style(
			$handle,
			Utils::get_asset_url( 'kudos-admin-settings.css' ),
			[ 'wp-components' ],
			$this->version
		);

		do_action( 'kudos_admin_settings_page_assets', $handle );
	}

	/**
	 * Assets common to all Kudos Table pages.
	 *
	 * @since 2.0.0
	 */
	private function kudos_table_page_assets(): string {

		$handle = $this->plugin_name . '-table';
		wp_enqueue_script(
			$handle,
			Utils::get_asset_url( 'kudos-admin-table.js' ),
			[ 'jquery' ],
			$this->version,
			false
		);

		return $handle;
	}

	/**
	 * Assets specific to the Kudos Transactions page.
	 *
	 * @since   2.0.0
	 */
	public function kudos_transactions_page_assets() {

		wp_enqueue_script(
			$this->plugin_name . '-transactions',
			Utils::get_asset_url( 'kudos-admin-transactions.js' ),
			[ 'jquery' ],
			$this->version,
			false
		);

		// Load table assets.
		$table_handle = $this->kudos_table_page_assets();
		wp_localize_script(
			$table_handle,
			'kudos',
			[
				'confirmationDelete' => __( 'Are you sure you want to delete this transaction?', 'kudos-donations' ),
			]
		);
	}

	/**
	 * Assets specific to the Kudos Subscriptions page.
	 *
	 * @since   2.0.0
	 */
	public function kudos_subscriptions_page_assets() {

		// Load table assets.
		$table_handle = $this->kudos_table_page_assets();
		wp_localize_script(
			$table_handle,
			'kudos',
			[
				'confirmationCancel' => __( 'Are you sure you want to cancel this subscription?', 'kudos-donations' ),
				'confirmationDelete' => __( 'Are you sure you want to delete this subscription?', 'kudos-donations' ),
			]
		);
	}

	/**
	 * Assets specific to the Kudos Donors page.
	 *
	 * @since   2.0.0
	 */
	public function kudos_donor_page_assets() {

		// Load table assets.
		$table_handle = $this->kudos_table_page_assets();
		wp_localize_script(
			$table_handle,
			'kudos',
			[
				'confirmationDelete' => __( 'Are you sure you want to delete this donor?', 'kudos-donations' ),
			]
		);
	}

	/**
	 * Assets specific to the Kudos Campaigns page.
	 *
	 * @since   2.0.0
	 */
	public function kudos_campaign_page_assets() {

		// Load table assets.
		$table_handle = $this->kudos_table_page_assets();
		wp_localize_script(
			$table_handle,
			'kudos',
			[
				'confirmationDelete' => __(
					'Are you sure you want to delete this campaign? This will not remove any transactions',
					'kudos-donations'
				),
			]
		);
	}

	/**
	 * Actions triggered by request data in the admin.
	 * Needs to be hooked to admin_init as it modifies headers.
	 *
	 * @since    1.0.1
	 */
	public function admin_actions() {

		if ( isset( $_REQUEST['kudos_action'] ) ) {

			$action = sanitize_text_field( wp_unslash( $_REQUEST['kudos_action'] ) );
			$nonce  = wp_unslash( $_REQUEST['_wpnonce'] );

			// Check nonce.
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				die();
			}

			switch ( $action ) {

				case 'kudos_log_download':
					LoggerService::download();
					break;

				case 'kudos_log_clear':
					if ( LoggerService::clear() === 0 ) {
						new AdminNotice( __( 'Log cleared', 'kudos-donations' ) );
					}

					break;

				case 'kudos_clear_mollie':
					$settings = new Settings();
					$settings->remove_setting( 'vendor_mollie' );
					$settings->add_defaults();
					break;

				case 'kudos_clear_campaigns':
					$settings = new Settings();
					$settings->remove_setting( 'campaigns' );
					$settings->add_defaults();
					break;

				case 'kudos_clear_all':
					$settings = new Settings();
					$settings->remove_settings();
					$settings->add_defaults();
					break;

				case 'kudos_clear_cache':
					$twig = TwigService::factory();
					if ( $twig->clearCache() ) {
						new AdminNotice( __( 'Cache cleared', 'kudos-donations' ) );
					}
					break;

				case 'kudos_clear_transactions':
					$mapper  = new MapperService( TransactionEntity::class );
					$records = $mapper->delete_all();
					if ( $records ) {
						new AdminNotice(
							sprintf(
							/* translators: %s: Number of records. */
								_n( 'Deleted %s transaction', 'Deleted %s transactions', $records, 'kudos-donations' ),
								$records
							)
						);
					}

					break;

				case 'kudos_clear_donors':
					$mapper  = new MapperService( DonorEntity::class );
					$records = $mapper->delete_all();
					if ( $records ) {
						new AdminNotice(
							sprintf(
							/* translators: %s: Number of records. */
								_n( 'Deleted %s donor', 'Deleted %s donors', $records, 'kudos-donations' ),
								$records
							)
						);
					}
					break;

				case 'kudos_clear_subscriptions':
					$mapper  = new MapperService( SubscriptionEntity::class );
					$records = $mapper->delete_all();
					if ( $records ) {
						new AdminNotice(
							sprintf(
							/* translators: %s: Number of records. */
								_n( 'Deleted %s subscription',
									'Deleted %s subscriptions',
									$records,
									'kudos-donations' ),
								$records
							)
						);
					}
					break;

				case 'kudos_recreate_database':
					$mapper = new MapperService();
					foreach (
						[
							SubscriptionEntity::get_table_name(),
							TransactionEntity::get_table_name(),
							DonorEntity::get_table_name(),
						] as $table
					) {
						$mapper->delete_table( $table );
					}
					ActivatorService::activate();
					new AdminNotice( __( 'Database re-created', 'kudos-donations' ) );
			}

			do_action( 'kudos_admin_actions_extra', $action );
		}

	}

	/**
	 * Register the kudos settings.
	 *
	 * @since 2.0.0
	 */
	public function register_settings() {

		$settings = new Settings();
		$settings->register_settings();

	}
}
