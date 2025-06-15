<?php
/**
 * Boilerplate repository rest controller.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Repository\BaseRepository;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

abstract class AbstractRepositoryRestController extends AbstractRestController {

	protected BaseRepository $repository;

	/**
	 * Specifies the base routes for repository endpoints.
	 */
	public function get_base_routes(): array {
		return [
			'/'            => [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => '__return_true',
					'args'                => [
						'paged'    => [
							'type'              => 'integer',
							'default'           => 1,
							'sanitize_callback' => 'absint',
						],
						'per_page' => [
							'type'              => 'integer',
							'default'           => 20,
							'sanitize_callback' => 'absint',
						],
						'order'    => [
							'type'              => FieldType::STRING,
							'default'           => 'DESC',
							'sanitize_callback' => 'sanitize_text_field',
						],
						'orderby'  => [
							'type'              => FieldType::STRING,
							'default'           => 'created_at',
							'sanitize_callback' => 'sanitize_text_field',
						],
						'where'    => [
							'type'              => 'object',
							'validate_callback' => '__return_true',
							'sanitize_callback' => '__return_true',
						],
					],
				],
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'can_manage_options' ],
					'args'                => [
						'title' => [
							'type'     => FieldType::STRING,
							'required' => true,
						],
					],
				],
			],
			'/(?P<id>\d+)' => [
				[
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'can_manage_options' ],
					'args'                => $this->get_args(),
				],
				[
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'can_manage_options' ],
					'args'                => [
						'id' => [
							'required' => true,
							'type'     => 'integer',
						],
					],
				],
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_routes(): array {
		return array_merge(
			$this->get_base_routes(),
			$this->get_additional_routes()
		);
	}

	/**
	 * Supply additional routes.
	 */
	protected function get_additional_routes(): array {
		return [];
	}

	protected function get_args(): array {
		return [];
	}

	/**
	 * Get the repository items.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function get_items( $request ): WP_REST_Response {
		$paged    = max( 1, (int) $request->get_param( 'paged' ) );
		$per_page = max( 1, (int) $request->get_param( 'per_page' ) );
		$orderby  = $request->get_param( 'orderby' );
		$order    = $request->get_param( 'order' );
		$offset   = ( $paged - 1 ) * $per_page;
		$column   = $request->get_param( 'column' );
		$value    = $request->get_param( 'value' );

		$where = [];
		if ( $column && null !== $value ) {
			$where[ $column ] = $value;
		}

		$args = [
			'limit'   => $per_page,
			'offset'  => $offset,
			'orderby' => $orderby,
			'order'   => $order,
			'where'   => $where,
		];

		$items = $this->repository->query( $args );
		$items = array_map( fn( $item ) => $this->add_rest_fields( $item ), $items );

		return new WP_REST_Response(
			[
				'items'       => $items,
				'total'       => $this->repository->count_query( $where ),
				'total_pages' => (int) ceil( $this->repository->count_query( $where ) / $per_page ),
				'per_page'    => $per_page,
				'paged'       => $paged,
			]
		);
	}

	/**
	 * Create a new entity.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		$params = $request->get_params();
		$data   = array_intersect_key( $params, array_flip( array_keys( $this->repository::get_column_schema() ) ) );
		$data   = array_map(
			function ( $value ) {
				return '' === $value ? null : $value;
			},
			$data
		);

		if ( $data['id'] ) {
			$id = $data['id'];
			unset( $data['id'] );
			$this->repository->update( $id, $data );
		} else {
			$id = $this->repository->insert( $data );
		}

		if ( ! $id ) {
			return new WP_Error( 'cannot_create', __( 'Could not create campaign.', 'kudos-donations' ), [ 'status' => 500 ] );
		}

		return new WP_REST_Response( $this->repository->find( $id ), 201 );
	}

	/**
	 * Update existing new entity.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$id   = (int) $request['id'];
		$data = $request->get_params();

		if ( ! $this->repository->update( $id, $data ) ) {
			return new WP_Error( 'cannot_update', __( 'Could not update campaign.', 'kudos-donations' ), [ 'status' => 500 ] );
		}

		return new WP_REST_Response( $this->repository->find( $id ), 200 );
	}

	/**
	 * Delete entity.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ): WP_REST_Response {
		$id = (int) $request->get_param( 'id' );

		$deleted = $this->repository->delete( $id );

		if ( ! $deleted ) {
			return new WP_REST_Response(
				[ 'message' => __( 'Failed to delete item.', 'kudos-donations' ) ],
				500
			);
		}

		return new WP_REST_Response(
			[ 'message' => __( 'Item deleted.', 'kudos-donations' ) ],
			200
		);
	}

	/**
	 * Optionally enrich items (e.g., join campaign, donor, etc).
	 * Override in child controllers.
	 *
	 * @param array $item Item to add to the response object.
	 */
	protected function add_rest_fields( array $item ): array {
		return $item;
	}
}
