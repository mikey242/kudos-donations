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

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Service\SettingsService;
use WP_REST_Request;
use WP_REST_Server;

class Admin extends AbstractRegistrable {

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
						update_option( SettingsService::SETTING_NAME_VENDOR_MOLLIE, [ 'mode' => 'test' ] );
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
								return preg_match( '/^SETTING_NAME/', $key );
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
				case 'kudos_clear_log':
					$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
					if ( wp_verify_nonce( $nonce, 'kudos_clear_log' ) ) {
						wp_delete_file( KUDOS_STORAGE_DIR . 'logs/' . $_ENV['APP_ENV'] . '.log' );
					}
					break;
				case 'kudos_clear_twig_cache':
					$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
					if ( wp_verify_nonce( $nonce, 'kudos_clear_twig_cache' ) ) {
						Utils::recursively_clear_cache( 'twig' );
					}
					break;
				case 'kudos_clear_container_cache':
					$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
					if ( wp_verify_nonce( $nonce, 'kudos_clear_container_cache' ) ) {
						Utils::recursively_clear_cache( 'container' );
					}
					break;
				default:
					$this->logger->debug( 'Action not implemented', [ 'action' => $action ] );
					break;
			}
		}
	}
}
