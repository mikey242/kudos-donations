<?php
/**
 * Admin related functions.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller;

use IseardMedia\Kudos\Admin\DebugAdminPage;
use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Service\CacheService;
use IseardMedia\Kudos\Service\SettingsService;
use IseardMedia\Kudos\Vendor\MollieVendor;
use WP_REST_Request;
use WP_REST_Server;

class Admin extends AbstractRegistrable {

	private CacheService $cache;

	/**
	 * Constructor for injecting services.
	 *
	 * @param CacheService $cache The cache service.
	 */
	public function __construct( CacheService $cache ) {
		$this->cache = $cache;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_actions(): array {
		return [ 'admin_init' ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->handle_query_variables();
	}

	/**
	 * Handles the various query variables and shows relevant modals.
	 */
	public function handle_query_variables(): void {
		if ( isset( $_REQUEST['kudos_action'] ) && - 1 !== $_REQUEST['kudos_action'] ) {
			$action = sanitize_text_field( wp_unslash( $_REQUEST['kudos_action'] ) );
			$this->logger->debug( 'Action requested', [ 'action' => $action ] );

			switch ( $action ) {
				case 'view_invoice':
					$nonce          = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
					$transaction_id = sanitize_text_field( $_REQUEST['id'] );
					$force          = rest_sanitize_boolean( $_REQUEST['force'] ?? false );
					if ( $transaction_id && wp_verify_nonce( $nonce, $action . '_' . $transaction_id ) ) {
						$request = new WP_REST_Request( WP_REST_Server::READABLE, "/kudos/v1/invoice/view/transaction/$transaction_id" );
						$request->set_param( 'force', $force );
						rest_do_request( $request );
					}
					break;
				case 'kudos_clear_mollie':
					$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
					if ( wp_verify_nonce( $nonce, 'kudos_clear_mollie' ) ) {
						update_option( MollieVendor::SETTING_VENDOR_MOLLIE, [ 'mode' => 'test' ] );
					}
					break;
				case 'kudos_clear_settings':
					$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
					if ( wp_verify_nonce( $nonce, 'kudos_clear_settings' ) ) {
						$reflection         = new \ReflectionClass( SettingsService::class );
						$constants          = $reflection->getConstants();
						$filtered_constants = array_filter(
							$constants,
							function ( $key ) {
								return preg_match( '/^SETTING/', $key );
							},
							ARRAY_FILTER_USE_KEY
						);
						foreach ( $filtered_constants as $setting_name ) {
							delete_option( $setting_name );
						}
					}
					break;
				case 'kudos_clear_campaigns':
					$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
					if ( wp_verify_nonce( $nonce, 'kudos_clear_campaigns' ) ) {
						$campaigns = CampaignPostType::get_posts();
						foreach ( $campaigns as $campaign ) {
							wp_delete_post( $campaign->ID, true );
						}
					}
					break;
				case 'kudos_clear_twig_cache':
					$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
					if ( wp_verify_nonce( $nonce, 'kudos_clear_twig_cache' ) ) {
						$this->cache->purge_cache( 'twig', __( 'User requested', 'kudos-donations' ) );
					}
					break;
				case 'kudos_clear_container_cache':
					$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
					if ( wp_verify_nonce( $nonce, 'kudos_clear_container_cache' ) ) {
						$this->cache->purge_cache( 'container', __( 'User requested', 'kudos-donations' ) );
					}
					break;
				case 'kudos_clear_all_cache':
					$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
					if ( wp_verify_nonce( $nonce, 'kudos_clear_all_cache' ) ) {
						$this->cache->purge_cache( null, __( 'User requested', 'kudos-donations' ) );
					}
					break;
				case 'kudos_clear_logs':
					$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
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
}
