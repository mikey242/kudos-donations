<?php
/**
 * Admin Notice Rest Routes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Service\NoticeService;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Notice extends AbstractRestController {

	/**
	 * PaymentRoutes constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->rest_base = 'notice';
	}

	/**
	 * Mail service routes.
	 */
	public function get_routes(): array {

		return [
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
	 * Removes the specified notice from the options array.
	 *
	 * @param WP_REST_Request $request Request array.
	 */
	public function dismiss_notice( WP_REST_Request $request ): WP_REST_Response {
		$key = $request->get_param( 'id' );
		if ( NoticeService::dismiss_notice( $key ) ) {
			return new WP_REST_Response( 'Notice dismissed', 200 );
		}
		return new WP_REST_Response( 'Notice not found', 404 );
	}
}
