<?php

namespace Kudos\Service;

use Kudos\Entity\AbstractEntity;
use Kudos\Entity\CampaignEntity;
use Kudos\Entity\EntityInterface;
use Kudos\Exceptions\MapperException;
use ReflectionClass;
use ReflectionException;
use WP_REST_Request;
use WP_REST_Response;
use wpdb;

class RestMapperService extends AbstractService {

	/**
	 * WordPress database global
	 *
	 * @var wpdb
	 */
	protected $wpdb;
	/**
	 * Repository class
	 *
	 * @var string
	 */
	protected $repository;

	/**
	 * Entity object constructor.
	 *
	 * @param string |null $repository Repository class.
	 *
	 * @since   2.0.0
	 */
	public function __construct( string $repository = null ) {

		parent::__construct();

		global $wpdb;
		$this->wpdb = $wpdb;
		if ( null !== $repository ) {
			try {
				$this->set_repository( $repository );
			} catch ( MapperException $e ) {
				$this->logger->error( 'Could not set repository', [ 'message' => $e->getMessage() ] );
			}
		}
	}

	/**
	 * Returns current repository table name
	 *
	 * @param bool $prefix Whether to return the prefix or not.
	 *
	 * @return string|false
	 * @since   2.0.0
	 */
	public function get_table_name( $prefix = true ) {

		return $this->get_repository()::get_table_name( $prefix );

	}

	/**
	 * Gets the current repository
	 *
	 * @return AbstractEntity|string
	 * @since 2.0.5
	 */
	public function get_repository() {

		try {
			if ( null === $this->repository ) {
				throw new MapperException( 'No repository specified' );
			}
		} catch ( MapperException $e ) {
			$this->logger->warning( 'Failed to get repository.', [ 'message' => $e->getMessage() ] );
		}

		return $this->repository;

	}

	/**
	 * Specify the repository to use
	 *
	 * @param string $class Class of repository to use.
	 *
	 * @throws MapperException
	 * @since 2.0.0
	 */
	public function set_repository( string $class ) {

		try {
			$reflection = new ReflectionClass( $class );
			if ( $reflection->implementsInterface( 'Kudos\Entity\EntityInterface' ) ) {
				$this->repository = $class;
			} else {
				throw new MapperException( 'Repository must implement Kudos\Entity\EntityInterface', 0, $class );
			}
		} catch ( ReflectionException $e ) {
			$this->logger->error( $e->getMessage() );
		}

	}

	/**
	 * Maps array of current repository objects to instance
	 * of current repository
	 *
	 * @param array $results Array of properties and values to map.
	 *
	 * @return array
	 * @since   2.0.0
	 */
	private function map_to_class( array $results ): array {

		return array_map(
			function ( $result ) {
				return new $this->repository( $result );
			},
			$results
		);

	}

	/**
	 * Removes empty values from array
	 *
	 * @param string|null $value Array value to check.
	 *
	 * @return bool
	 * @since 2.0.0
	 */
	private function remove_empty( ?string $value ): bool {

		return ! is_null( $value ) && '' !== $value;

	}

	/**
	 * Updates existing record
	 *
	 * @param WP_REST_Request $entity An instance of EntityInterface.
	 *
	 * @return WP_REST_Response Returns the id of the record if successful
	 *                  and false if not
	 */
	public function update_record( WP_REST_Request $entity ): WP_REST_Response {

		$table_name = CampaignEntity::get_table_name();
		$wpdb = $this->wpdb;

		$entity = json_decode($entity);
		$result = $wpdb->update(
			$table_name,
			$entity,
			[ 'id' => $entity['id'] ]
		);

		return new WP_REST_Response(
			[
				'status' => 200,
				'response' => 'hello',
				'body_response' => $entity,
			]
		);

//		$wpdb       = $this->wpdb;
//		$table_name = $entity::get_table_name();
//		$id         = $entity->id;
//
//		$this->logger->debug( 'Updating entity.', [ $entity ] );
//
//		$result = $wpdb->update(
//			$table_name,
//			$ignore_null ? array_filter( $entity->to_array(), [ $this, 'remove_empty' ] ) : $entity->to_array(),
//			[ 'id' => $id ]
//		);
//
//		if ( $result ) {
//			do_action( $entity::get_table_name( false ) . '_updated', 'id', $id );
//
//			return $id;
//		}
//
//		return $result;
	}

	public function get_all(): WP_REST_Response {

		$table        = $this->get_table_name();
		$query        = "SELECT $table.* FROM $table";

		$results = $this->get_results( $query, ARRAY_A );

		if ( ! empty( $results ) ) {
			return new WP_REST_Response(
				[
					'status' => 200,
					'response' => 'hello',
					'body_response' => json_encode($this->map_to_class( $results ))
				]
			);
		}

		return new WP_REST_Response(
			[
				'status' => 200,
				'response' => 'hello',
				'body_response' => '',
			]
		);
	}
}
