<?php

namespace Kudos\Controller;

use Kudos\Controller\Table\CampaignsTable;
use Kudos\Controller\Table\DonorsTable;
use Kudos\Controller\Table\SubscriptionsTable;
use Kudos\Controller\Table\TransactionsTable;
use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Assets;
use Kudos\Helpers\Settings;
use Kudos\Service\ActivatorService;
use Kudos\Service\AdminNotice;
use Kudos\Service\LoggerService;
use Kudos\Service\MapperService;
use Kudos\Service\PaymentService;
use Kudos\Service\RestRouteService;
use Kudos\Service\TwigService;
use Kudos\Service\Vendor\MollieVendor;

class Admin {
	/**
	 * The version of this plugin.
	 *
	 * @var string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * @var MapperService
	 */
	private $mapper;
	/**
	 * @var TransactionsTable
	 */
	private $table;
	/**
	 * @var TwigService
	 */
	private $twig;
	/**
	 * @var PaymentService
	 */
	private $payment;
	/**
	 * @var Settings
	 */
	private $settings;
	/**
	 * @var ActivatorService
	 */
	private $activator;
	/**
	 * @var \Kudos\Service\Vendor\MollieVendor
	 */
	private $mollie;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $version The version of this plugin.
	 */
	public function __construct(
		string $version,
		MapperService $mapper,
		TwigService $twig,
		PaymentService $payment,
		ActivatorService $activator,
		Settings $settings,
		MollieVendor $mollie_vendor
	) {
		$this->version     = $version;
		$this->mapper      = $mapper;
		$this->twig        = $twig;
		$this->payment     = $payment;
		$this->activator   = $activator;
		$this->settings    = $settings;
		$this->mollie      = $mollie_vendor;
	}

	/**
	 * Create the Kudos Donations admin pages.
	 */
	public function add_menu_pages() {

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

		/*
		 * Settings page.
		 */
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

		add_action( "load-$settings_page_hook_suffix", [ $this, 'prepare_settings_page' ] );

		/*
		 * Transaction page.
		 */
		$transactions_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf( __( '%s Transactions', 'kudos-donations' ), 'Kudos' ),
			__( 'Transactions', 'kudos-donations' ),
			'manage_options',
			'kudos-transactions',
			function () {
				include_once KUDOS_PLUGIN_DIR . '/app/View/kudos-admin-transactions.php';
			}
		);

		add_action( "load-$transactions_page_hook_suffix", [ $this, 'prepare_transactions_page' ] );

		/*
		 * Subscription page.
		 */
		$subscriptions_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf( __( '%s Subscriptions', 'kudos-donations' ), 'Kudos' ),
			__( 'Subscriptions', 'kudos-donations' ),
			'manage_options',
			'kudos-subscriptions',
			function () {
				include_once KUDOS_PLUGIN_DIR . '/app/View/kudos-admin-subscriptions.php';
			}
		);

		add_action( "load-$subscriptions_page_hook_suffix", [ $this, 'prepare_subscriptions_page' ] );

		/*
		 * Donor page.
		 */
		$donors_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf( __( '%s Donors', 'kudos-donations' ), 'Kudos' ),
			__( 'Donors', 'kudos-donations' ),
			'manage_options',
			'kudos-donors',
			function () {
				include_once KUDOS_PLUGIN_DIR . '/app/View/kudos-admin-donors.php';
			}

		);

		add_action( "load-$donors_page_hook_suffix", [ $this, 'prepare_donors_page' ] );

		/*
		 * Campaign page.
		 */
		$campaigns_page_hook_suffix = add_submenu_page(
			'kudos-settings',
			/* translators: %s: Plugin name */
			sprintf( __( '%s Campaigns', 'kudos-donations' ), 'Kudos' ),
			__( 'Campaigns', 'kudos-donations' ),
			'manage_options',
			'kudos-campaigns',
			function () {
				include_once KUDOS_PLUGIN_DIR . '/app/View/kudos-admin-campaigns.php';
			}

		);

		add_action( "load-$campaigns_page_hook_suffix", [ $this, 'prepare_campaigns_page' ] );

		/*
		 * Debug page.
		 */
		$debug_page_hook_suffix = add_submenu_page(
			KUDOS_DEBUG ? 'kudos-settings' : null,
			'Kudos Debug',
			'Debug',
			'manage_options',
			'kudos-debug',
			function () {
				require_once KUDOS_PLUGIN_DIR . '/app/View/kudos-admin-debug.php';
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
	 * Hook settings page assets.
	 */
	public function prepare_settings_page() {
		add_action( 'admin_enqueue_scripts', [ $this, 'settings_page_assets' ] );
	}

	/**
	 * Hook assets and prepare the table for screen options.
	 */
	public function prepare_transactions_page() {
		add_action( "admin_enqueue_scripts", [ $this, "transactions_page_assets" ] );
		$this->table = new TransactionsTable( $this->mapper );
		$this->table->prepare_items();
	}

	/**
	 * Hook assets and prepare the table for screen options.
	 */
	public function prepare_subscriptions_page() {
		add_action( "admin_enqueue_scripts", [ $this, 'subscriptions_page_assets' ] );
		$this->table = new SubscriptionsTable( $this->mapper, $this->payment );
		$this->table->prepare_items();
	}

	/**
	 * Hook assets and prepare the table for screen options.
	 */
	public function prepare_donors_page() {
		add_action( "admin_enqueue_scripts", [ $this, 'donor_page_assets' ] );
		$this->table = new DonorsTable( $this->mapper );
		$this->table->prepare_items();
	}

	/**
	 * Hook assets and prepare the table for screen options.
	 */
	public function prepare_campaigns_page() {
		add_action( "admin_enqueue_scripts", [ $this, 'campaign_page_assets' ] );
		$this->table = new CampaignsTable( $this->mapper, $this->settings );
		$this->table->prepare_items();
	}

	/**
	 * Register assets for enqueuing in the block editor.
	 */
	public function register_block_editor_assets() {

		wp_register_style(
			'kudos-donations-public',
			Assets::get_asset_url( '/public/kudos-public.css' ),
			[],
			$this->version
		);

		$editor_js = Assets::get_script( '/blocks/kudos-button/index.js' );
		wp_register_script(
			'kudos-donations-editor',
			$editor_js['url'],
			$editor_js['dependencies'],
			$editor_js['version'],
			true
		);
	}

	/**
	 * Assets specific to the Settings page.
	 */
	public function settings_page_assets() {

		$handle = 'kudos-donations-settings';

		// Enqueue the styles
		wp_enqueue_style(
			$handle,
			Assets::get_asset_url( '/admin/kudos-admin-settings.css' ),
			[ 'wp-components' ],
			$this->version
		);

		// Get and enqueue the script
		$admin_js = Assets::get_script( '/admin/kudos-admin-settings.js' );
		wp_enqueue_script(
			$handle,
			$admin_js['url'],
			$admin_js['dependencies'],
			$admin_js['version'],
			true
		);

		wp_localize_script(
			$handle,
			'kudos',
			[
				'version'     => $this->version,
				'checkApiUrl' => rest_url( RestRouteService::NAMESPACE . RestRouteService::PAYMENT_TEST ),
				'sendTestUrl' => rest_url( RestRouteService::NAMESPACE . RestRouteService::EMAIL_TEST ),
			]
		);
		wp_set_script_translations( $handle, 'kudos-donations', KUDOS_PLUGIN_DIR . '/languages' );

		do_action( 'kudos_admin_settings_page_assets', $handle );
	}

	/**
	 * Assets common to all Table pages.
	 */
	private function table_page_assets(): string {

		$handle   = 'kudos-donations-table';
		$table_js = Assets::get_script( '/admin/kudos-admin-table.js' );

		wp_enqueue_script(
			$handle,
			$table_js['url'],
			$table_js['dependencies'],
			$table_js['version'],
			false
		);

		return $handle;
	}

	/**
	 * Assets specific to the Kudos Transactions page.
	 */
	public function transactions_page_assets() {

		$transactions_js = Assets::get_script( '/admin/kudos-admin-transactions.js' );

		wp_enqueue_script(
			'kudos-donations-transactions',
			$transactions_js['url'],
			$transactions_js['dependencies'],
			$transactions_js['version'],
			false
		);

		// Load table assets.
		$table_handle = $this->table_page_assets();
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
	 */
	public function subscriptions_page_assets() {

		// Load table assets.
		$table_handle = $this->table_page_assets();
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
	 */
	public function donor_page_assets() {

		// Load table assets.
		$table_handle = $this->table_page_assets();
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
	 */
	public function campaign_page_assets() {

		// Load table assets.
		$table_handle = $this->table_page_assets();
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
	 */
	public function admin_actions() {

		if ( isset( $_REQUEST['kudos_action'] ) ) {

			$action = sanitize_text_field( wp_unslash( $_REQUEST['kudos_action'] ) );
			$nonce  = wp_unslash( $_REQUEST['_wpnonce'] );

			// Check nonce.
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				die();
			}

			$settings = $this->settings;

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
					$settings->remove_setting( 'vendor_mollie' );
					$settings->add_defaults();
					break;

				case 'kudos_clear_campaigns':
					$settings->remove_setting( 'campaigns' );
					$settings->add_defaults();
					break;

				case 'kudos_clear_all':
					$settings->remove_settings();
					$settings->add_defaults();
					break;

				case 'kudos_clear_twig_cache':
					if ( $this->twig->clearCache() ) {
						new AdminNotice( __( 'Cache cleared', 'kudos-donations' ) );
					}
					break;

				case 'kudos_clear_object_cache':
					if ( wp_cache_flush() ) {
						new AdminNotice( __( 'Cache cleared', 'kudos-donations' ) );
					}
					break;

				case 'kudos_clear_transactions':
					$records = $this->mapper
						->get_repository( TransactionEntity::class )
						->delete_all();
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
					$records = $this->mapper
						->get_repository( DonorEntity::class )
						->delete_all();
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
					$records = $this->mapper
						->get_repository( SubscriptionEntity::class )
						->delete_all();
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
					foreach (
						[
							SubscriptionEntity::get_table_name(),
							TransactionEntity::get_table_name(),
							DonorEntity::get_table_name(),
						] as $table
					) {
						$this->mapper->delete_table( $table );
					}
					$activator = $this->activator;
					$activator->activate();
					new AdminNotice( __( 'Database re-created', 'kudos-donations' ) );
					break;

				case 'kudos_sync_payments':
					$mollie = $this->mollie;
					$updated = $mollie->sync_transactions();
					if($updated) {
						new AdminNotice(
							sprintf(
							/* translators: %s: Number of records. */
								_n( 'Updated %s transaction',
									'Updated %s transactions',
									$updated,
									'kudos-donations' ),
								$updated
							)
						);
						break;
					}
					new AdminNotice(__('No transactions need updating', 'kudos-donations'));
			}

			do_action( 'kudos_admin_actions_extra', $action );
		}

	}

	/**
	 * Removes the secret for the specified entity where
	 * it matches the provided id.
	 *
	 * @param string $entity The class of entity.
	 * @param string $id The ID of row.
	 *
	 * @return bool|int
	 */
	public function remove_secret_action( string $entity, string $id ) {

		if ( $id ) {
			$mapper = $this->mapper;
			$entity = $mapper
				->get_repository( $entity )
				->get_one_by( [ 'id' => $id ] );
			if ( ! $entity ) {
				return false;
			}
			$entity->clear_secret();

			return $mapper->save( $entity, false );
		}

		return false;
	}

	/**
	 * Clear the log file when over certain size.
	 *
	 * @return bool|int
	 */
	public function clear_log() {

		$file = LoggerService::LOG_FILE;
		$size = filesize( $file );

		if ( $size >= LoggerService::LOG_SIZE ) {
			return LoggerService::clear();
		}

		return false;
	}

	/**
	 * Register the kudos settings.
	 */
	public function register_settings() {

		$settings = $this->settings;
		$settings->register_settings();

	}
}
