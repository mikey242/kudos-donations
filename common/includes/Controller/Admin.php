<?php
/**
 * Admin related functions.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller;

use IseardMedia\Kudos\Admin\DebugAdminPage;
use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\Handler\SettingsHandler;
use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Service\CacheService;
use IseardMedia\Kudos\Service\NoticeService;
use WP_REST_Request;
use WP_REST_Server;

class Admin extends AbstractRegistrable {

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action(): string {
		return 'admin_init';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->handle_query_variables();
		$this->show_notices();
	}

	/**
	 * Handles the various query variables and shows relevant modals.
	 */
	public function handle_query_variables(): void {
		if ( isset( $_REQUEST['kudos_action'] ) && - 1 !== $_REQUEST['kudos_action'] ) {
			$action = sanitize_text_field( wp_unslash( $_REQUEST['kudos_action'] ) );
			$this->logger->debug( 'Action requested', [ 'action' => $action ] );
			$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

			switch ( $action ) {
				case 'view_invoice':
					$transaction_id = isset( $_REQUEST['id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) : '';
					$force          = isset( $_REQUEST['force'] ) && rest_sanitize_boolean( wp_unslash( $_REQUEST['force'] ) );
					if ( $transaction_id && wp_verify_nonce( $nonce, $action . '_' . $transaction_id ) ) {
						$request = new WP_REST_Request( WP_REST_Server::READABLE, "/kudos/v1/invoice/get/transaction/$transaction_id" );
						$request->set_param( 'force', $force );
						$request->set_param( 'view', true );
						rest_do_request( $request );
					}
					break;
				case 'kudos_clear_settings':
					if ( wp_verify_nonce( $nonce, 'kudos_clear_settings' ) ) {
						global $new_allowed_options;
						$settings = $new_allowed_options[ SettingsHandler::GROUP ];
						foreach ( $settings as $setting_name ) {
							delete_option( $setting_name );
						}
					}
					break;
				case 'kudos_clear_campaigns':
					if ( wp_verify_nonce( $nonce, 'kudos_clear_campaigns' ) ) {
						$campaigns = CampaignPostType::get_posts();
						foreach ( $campaigns as $campaign ) {
							wp_delete_post( $campaign->ID, true );
						}
					}
					break;
				case 'kudos_clear_twig_cache':
					if ( wp_verify_nonce( $nonce, 'kudos_clear_twig_cache' ) ) {
						CacheService::recursively_clear_cache( 'twig' );
					}
					break;
				case 'kudos_clear_container_cache':
					if ( wp_verify_nonce( $nonce, 'kudos_clear_container_cache' ) ) {
						CacheService::recursively_clear_cache( 'container' );
					}
					break;
				case 'kudos_clear_all_cache':
					if ( wp_verify_nonce( $nonce, 'kudos_clear_all_cache' ) ) {
						CacheService::recursively_clear_cache();
					}
					break;
				case 'kudos_clear_logs':
					if ( wp_verify_nonce( $nonce, 'kudos_clear_logs' ) ) {
						$log_files = DebugAdminPage::get_logs();
						foreach ( $log_files as $log_file ) {
							wp_delete_file( $log_file );
						}
					}
					break;
				default:
					$this->logger->debug( 'Action not implemented', [ 'action' => $action ] );
					break;
			}
		}
	}

	/**
	 * Shows admin notices stored in the option.
	 */
	public function show_notices(): void {
		$notices = NoticeService::get_notices();
		if ( $notices ) {
			add_action(
				'admin_print_footer_scripts',
				function () {
					echo '
					<script type="text/javascript">
						const notices = document.querySelectorAll(".kudos-notice")
						console.log(notices)
						notices.forEach((notice) => {
                            notice.addEventListener("click", function() {
                                const key = notice.dataset.noticeKey;
                                fetch("' . esc_url( rest_url( '/kudos/v1/notice/dismiss' ) ) . '", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                		"X-WP-Nonce": "' . esc_attr( wp_create_nonce( 'wp_rest' ) ) . '"
                                    },
                                    body: JSON.stringify({ id: key })
                                })
                            })
						})
					</script>
				';
				}
			);
			foreach ( $notices as $key => $notice ) {
				$message     = $notice['message'] ?? '';
				$level       = $notice['level'] ?? NoticeService::INFO;
				$dismissible = $notice['dismissible'] ?? true;
				$logo        = $notice['logo'] ?? true;
				NoticeService::notice(
					$message,
					$level,
					$dismissible,
					$key,
					$logo
				);
			}
		}
	}
}