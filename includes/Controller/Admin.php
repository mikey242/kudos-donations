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

use IseardMedia\Kudos\Infrastructure\Container\AbstractService;
use WP_REST_Request;
use WP_REST_Server;

class Admin extends AbstractService {

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

			switch ( $action ) {
				case 'view_invoice':
					$nonce          = sanitize_text_field( wp_unslash( $_REQUEST['_wp_nonce'] ) );
					$transaction_id = sanitize_text_field( $_REQUEST['id'] );
					$force          = rest_sanitize_boolean( $_REQUEST['force'] ?? false );
					if ( $transaction_id && wp_verify_nonce( $nonce, $action . '_' . $transaction_id ) ) {
						$request = new WP_REST_Request( WP_REST_Server::READABLE, "/kudos/v1/invoice/view/transaction/$transaction_id" );
						$request->set_param( 'force', $force );
						rest_do_request( $request );
					}
					break;
				case 'default':
					break;
			}
		}
	}
}
