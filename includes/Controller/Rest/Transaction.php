<?php
/**
 * Transaction Rest Routes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Transaction extends AbstractRestController {

	/**
	 * Route constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->rest_base = 'transaction';
	}

	/**
	 * TransactionPostType routes.
	 */
	public function get_routes(): array {
		return [
			''     => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_all' ],
				'permission_callback' => '__return_true',
			],
			'/get' => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_one' ],
				'args'                => [
					'id' => [
						'type'              => FieldType::STRING,
						'required'          => true,
						'sanitize_callback' => 'absint',
					],
				],
				'permission_callback' => '__return_true',
			],
		];
	}

	/**
	 * Get one by id.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function get_one( WP_REST_Request $request ): WP_REST_Response {
		return new WP_REST_Response(
			get_post( $request['id'] )
		);
	}

	/**
	 * Get all records.
	 */
	public function get_all(): WP_REST_Response {
		return new WP_REST_Response(
			TransactionPostType::get_posts(
				[ TransactionPostType::META_FIELD_STATUS => PaymentStatus::PAID ]
			)
		);
	}
}
