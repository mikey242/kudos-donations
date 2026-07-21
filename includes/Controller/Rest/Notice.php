<?php
/**
 * Admin Notice Rest Routes.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Notice\NoticeManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Notice extends BaseRestController {

	protected string $rest_base = 'notice';

	/**
	 * Notice routes.
	 */
	public function get_routes(): array {

		return [
			''         => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_notices' ],
				'permission_callback' => [ $this, 'can_manage_options' ],
			],
			'/dismiss' => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'dismiss_notice' ],
				'args'                => [
					'id' => [
						'type'              => FieldType::STRING,
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
				'permission_callback' => [ $this, 'can_manage_options' ],
			],
		];
	}

	/**
	 * Returns all current notices formatted for the frontend.
	 */
	public function get_notices(): WP_REST_Response {
		return new WP_REST_Response( NoticeManager::get_notices_for_rest(), 200 );
	}

	/**
	 * Removes the specified notice from the options array.
	 *
	 * @param WP_REST_Request $request Request array.
	 */
	public function dismiss_notice( WP_REST_Request $request ): WP_REST_Response {
		NoticeManager::dismiss_notice( $request->get_param( 'id' ) );
		return new WP_REST_Response( [ 'message' => __( 'Notice dismissed.', 'kudos-donations' ) ], 200 );
	}
}
