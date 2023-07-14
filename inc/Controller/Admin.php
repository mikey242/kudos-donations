<?php
/**
 * Admin related functions.
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller;

use Exception;
use IseardMedia\Kudos\Admin\Notice\AdminDismissibleNotice;
use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Helper\Settings;
use IseardMedia\Kudos\Infrastructure\Container\AbstractService;
use IseardMedia\Kudos\Service\MigratorService;
use IseardMedia\Kudos\Service\TwigService;
use IseardMedia\Kudos\Service\Vendor\MollieVendor;
use Psr\Log\LoggerInterface;

class Admin extends AbstractService {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param TwigService     $twig Twig service.
	 * @param MollieVendor    $mollie_vendor Mollie vendor.
	 * @param LoggerInterface $logger Logger instance.
	 * @param MigratorService $migrator Migrator service.
	 */
	public function __construct(
		private TwigService $twig,
		private MollieVendor $mollie_vendor,
		private LoggerInterface $logger,
		private MigratorService $migrator
	) {}

	public function register(): void {
		add_action( 'admin_init', [ $this, 'register_block_editor_assets' ] );
		add_action( 'kudos_remove_secret_action', [ $this, 'remove_secret_action' ], 10, 2 );
		add_action( 'kudos_check_log', [ $this, 'truncate_log' ] );
	}

	/**
	 * Actions triggered by request data in the admin.
	 * Needs to be hooked to admin_init as it modifies headers.
	 */
	public function admin_actions(): void {
		if ( isset( $_REQUEST['kudos_action'] ) ) {
			$action = sanitize_text_field( wp_unslash( $_REQUEST['kudos_action'] ) );
			$nonce  = wp_unslash( $_REQUEST['_wpnonce'] );

			// Check nonce.
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				die();
			}

			switch ( $action ) {
				case 'kudos_clear_mollie':
					Settings::remove_setting( 'vendor_mollie' );
					break;

				case 'kudos_clear_all':
					Settings::remove_settings();
					break;

				case 'kudos_clear_twig_cache':
					if ( $this->twig->clearCache() ) {
						$notice = new AdminDismissibleNotice();
						$notice->success( __( 'Cache cleared', 'kudos-donations' ) );
					}
					break;

				case 'kudos_clear_object_cache':
					if ( wp_cache_flush() ) {
						$notice = new AdminDismissibleNotice();
						$notice->success( __( 'Cache cleared', 'kudos-donations' ) );
					}
					break;

				case 'kudos_sync_mollie_transactions':
					$mollie  = $this->mollie_vendor;
					$updated = $mollie->sync_transactions();
					if ( $updated ) {
						( new AdminDismissibleNotice() )->success(
							sprintf(
							/* translators: %s: Number of records. */
								_n(
									'Updated %s transaction',
									'Updated %s transactions',
									$updated,
									'kudos-donations'
								),
								$updated
							)
						);
						break;
					}
					( new AdminDismissibleNotice() )->success( __( 'No transactions need updating', 'kudos-donations' ) );
					break;

				case 'kudos_add_missing_mollie_transactions':
					$mollie  = $this->mollie_vendor;
					$updated = $mollie->add_missing_transactions();
					if ( $updated ) {
						( new AdminDismissibleNotice() )->success(
							sprintf(
							/* translators: %s: Number of records. */
								_n(
									'Added %s transaction',
									'Added %s transactions',
									$updated,
									'kudos-donations'
								),
								$updated
							)
						);
						break;
					}
					( new AdminDismissibleNotice() )->success( __( 'No transactions need adding', 'kudos-donations' ) );
					break;

				case 'kudos_migrate':
					if ( isset( $_REQUEST['migration_version'] ) ) {
						$versions = wp_unslash( $_REQUEST['migration_version'] );
					} else {
						$versions = Settings::get_setting( 'migrations_pending' );
					}
					if ( $versions ) {
						foreach ( $versions as $version ) {
							try {
								$this->migrator->migrate( $version );
							} catch ( Exception $e ) {
								$notice = new AdminDismissibleNotice();
								$notice->warning( $e->getMessage() );
							}
							if ( ( $key = array_search( $version, $versions ) ) !== false ) {
								unset( $versions[ $key ] );
							}
							Settings::update_setting( 'migrations_pending', $versions );
						}
					}
			}
		}
	}

	/**
	 * Register assets for enqueuing in the block editor.
	 */
	public function register_block_editor_assets(): void {
		wp_register_style(
			'kudos-donations-public',
			Assets::get_style( 'admin/kudos-admin-campaigns.jsx.css' ),
			[],
			KUDOS_VERSION
		);
	}

	/**
	 * Create the Kudos Donations admin pages.
	 */
	public function add_menu_pages(): void {
		$this->redirect_to_settings();
		$parent_slug = apply_filters( 'kudos_parent_settings_slug', 'kudos-campaigns' );

		/*
		 * Debug page.
		 */
		$debug_page_hook_suffix = add_submenu_page(
			$parent_slug,
			'Kudos Tools',
			'Tools',
			'manage_options',
			'kudos-tools',
			function (): void {
				require_once KUDOS_PLUGIN_DIR . '/inc/View/kudos-admin-tools.php';
			}
		);

		add_action(
			"admin_print_scripts-$debug_page_hook_suffix",
			function (): void {
				?>
				<script>
					document.addEventListener("DOMContentLoaded", function () {
						let buttons = document.querySelectorAll('button[type="submit"].confirm')
						for (let i = 0; i < buttons.length; i++) {
							buttons[i].addEventListener('click', function (e) {
								if (!confirm('<?php _e( 'Are you sure?', 'kudos-donations' ); ?>')) {
									e.preventDefault()
								}
							})
						}
					})
				</script>
				<?php
			}
		);
	}

	public function redirect_to_settings(): void {
		$show_intro = Settings::get_setting( 'show_intro', true );
		if ( $show_intro ) {
			global $pagenow;
			if ( $pagenow === 'admin.php' && $_GET['page'] === 'kudos-campaigns' ) {
				wp_redirect( admin_url( 'admin.php?page=kudos-settings' ) );
			}
		}
	}

	/**
	 * Truncates the log file when over certain length.
	 * Length defined by LoggerService::TRUNCATE_AT const.
	 */
	public function truncate_log(): void {
		$this->logger->truncate();
	}
}
