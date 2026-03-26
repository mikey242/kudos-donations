<?php
/**
 * Licence Rest Routes.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Service\LicenceService;
use WP_REST_Response;
use WP_REST_Server;

class Licence extends BaseRestController {

	protected string $rest_base = 'licence';
	private LicenceService $licence_service;

	/**
	 * Licence constructor.
	 *
	 * @param LicenceService $licence_service The licence service.
	 */
	public function __construct( LicenceService $licence_service ) {
		$this->licence_service = $licence_service;
	}

	/**
	 * Licence routes.
	 */
	public function get_routes(): array {
		return [
			'/install-addon' => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'install_addon' ],
				'permission_callback' => [ $this, 'is_licence_active_and_admin' ],
			],
		];
	}

	/**
	 * Installs and activates the premium add-on.
	 */
	public function install_addon(): WP_REST_Response {
		$licence_key = get_option( LicenceService::SETTING_KUDOS_LICENCE_KEY, '' );
		$success     = $this->licence_service->maybe_install_addon( $licence_key );

		if ( $success ) {
			return new WP_REST_Response(
				[ 'message' => __( 'Add-on installed and activated successfully.', 'kudos-donations' ) ],
				200
			);
		}

		return new WP_REST_Response(
			[ 'message' => __( 'Failed to install add-on. Please check the logs for details.', 'kudos-donations' ) ],
			500
		);
	}
}
