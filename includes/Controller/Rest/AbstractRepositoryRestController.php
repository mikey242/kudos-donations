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
					'permission_callback' => $this->can_list(),
					'args'                => [
						'columns'  => [
							'type'     => 'array',
							'required' => false,
						],
						'paged'    => [
							'type'              => FieldType::INTEGER,
							'default'           => 1,
							'sanitize_callback' => 'absint',
						],
						'per_page' => [
							'type'              => FieldType::INTEGER,
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
							'type'              => FieldType::OBJECT,
							'validate_callback' => '__return_true',
							'sanitize_callback' => '__return_true',
						],
					],
				],
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => $this->can_create(),
					'args'                => $this->get_rest_args(),
				],
			],
			'/(?P<id>\d+)' => [
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => $this->can_read_one(),
					'args'                => [
						'id' => [
							'required' => true,
							'type'     => FieldType::INTEGER,
						],
					],
				],
				[
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => $this->can_delete(),
					'args'                => [
						'id' => [
							'required' => true,
							'type'     => FieldType::INTEGER,
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

	/**
	 * Generates rest route args based on schema.
	 */
	public function get_rest_args(): array {
		$schema = $this->repository->get_column_schema();
		$args   = [];

		foreach ( $schema as $key => $field ) {
			$type = $field['type'];
			switch ( $type ) {
				case FieldType::FLOAT:
					$rest_type = 'number';
					break;
				default:
					$rest_type = $type;
					break;
			}

			$args[ $key ] = [
				'type'              => $rest_type,
				'default'           => $field['default'] ?? null,
				'sanitize_callback' => $field['sanitize_callback'] ?? null,
			];
		}

		return $args;
	}

	/**
	 * Get a single entity by ID.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$id   = (int) $request->get_param( 'id' );
		$item = $this->repository->find( $id );

		if ( ! $item ) {
			return new WP_Error( 'not_found', __( 'Campaign not found.', 'kudos-donations' ), [ 'status' => 404 ] );
		}

		return new WP_REST_Response( $this->add_rest_fields( $item ), 200 );
	}

	/**
	 * Get the repository items.
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function get_items( $request ): WP_REST_Response {
		$paged    = max( 1, (int) $request->get_param( 'paged' ) );
		$per_page = max( 1, (int) $request->get_param( 'per_page' ) );
		$columns  = $request->get_param( 'columns' );
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
			'columns' => $columns,
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
		$data   = array_intersect_key( $params, array_flip( array_keys( $this->repository->get_column_schema() ) ) );
		$data   = array_map(
			function ( $value ) {
				return '' === $value ? null : $value;
			},
			$data
		);

		// Create/update record.
		$id = $this->repository->upsert( $data );

		if ( ! $id ) {
			return new WP_Error( 'cannot_create', __( 'Could not create campaign.', 'kudos-donations' ), [ 'status' => 500 ] );
		}

		return new WP_REST_Response( $this->repository->find( $id ), 201 );
	}

	/**
	 * Delete entity.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ): WP_REST_Response {
		$id      = (int) $request->get_param( 'id' );
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

	/**
	 * Specifies who can read a specified record of this entity.
	 */
	protected function can_read_one(): callable {
		return [ $this, 'can_manage_options' ];
	}

	/**
	 * Specifies who can list all records of this entity.
	 */
	protected function can_list(): callable {
		return [ $this, 'can_manage_options' ];
	}

	/**
	 * Specifies who can create records of this entity.
	 */
	protected function can_create(): callable {
		return [ $this, 'can_edit_posts' ];
	}

	/**
	 * Specifies who can delete records of this entity.
	 */
	protected function can_delete(): callable {
		return [ $this, 'can_edit_posts' ];
	}
}
