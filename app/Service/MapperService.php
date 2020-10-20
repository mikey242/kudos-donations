<?php

namespace Kudos\Service;

use Kudos\Entity\AbstractEntity;
use Kudos\Entity\EntityInterface;
use Kudos\Exceptions\MapperException;
use ReflectionClass;
use ReflectionException;
use wpdb;

class MapperService extends AbstractService {

	/**
	 * @var wpdb
	 */
	protected $wpdb;
	/**
	 * @var AbstractEntity
	 */
	protected $repository;

	/**
	 * Entity object constructor.
	 *
	 * @param EntityInterface|null $repository
	 *
	 * @since   2.0.0
	 */
	public function __construct( $repository = null ) {

		parent::__construct();

		global $wpdb;
		$this->wpdb = $wpdb;
		if ( null !== $repository ) {
			$this->set_repository( $repository );
		}

	}

	/**
	 * Specify the repository to use
	 *
	 * @param string $class
	 * @since 2.0.0
	 */
	public function set_repository( string $class ) {

		try {
			$reflection = new ReflectionClass( $class );
			if($reflection->implementsInterface('Kudos\Entity\EntityInterface')) {
				$this->repository = $class;
			} else {
				throw new MapperException('Repository must implement Kudos\Entity\EntityInterface', 0, $class);
			}
		} catch ( ReflectionException | MapperException $e ) {
			$this->logger->error("Could not set repository", ["message" => $e->getMessage()]);
		}

	}

	/**
	 * Gets the current repository
	 *
	 * @return AbstractEntity
	 * @throws MapperException
	 * @since 2.0.5
	 */
	public function get_repository() {

		if ( NULL === $this->repository ) {
			throw new MapperException( "No repository specified" );
		}

		return $this->repository;

	}

	/**
	 * Commit Entity to database
	 *
	 * @param AbstractEntity $entity
	 * @param bool $ignore_empty
	 *
	 * @return bool|false|int
	 * @since   2.0.0
	 */
	public function save( AbstractEntity $entity, $ignore_empty = true ) {

		$wpdb                 = $this->wpdb;
		$table                = $entity::get_table_name();
		$entity->last_updated = current_time( 'mysql' );

		// If we have an id, then update row
		if ( $entity->id ) {
			$this->logger->debug( 'Updating entity.', [ $entity ] );

			$result = $wpdb->update(
				$table,
				$ignore_empty ? array_filter( $entity->to_array(), [ $this, 'remove_empty' ] ) : $entity->to_array(),
				[ 'id' => $entity->id ]
			);

			if ( $result ) {
				do_action( $entity::TABLE . '_updated', 'id', $entity->id );
			}

			return $result;
		}

		// Otherwise insert new row
		$entity->created = current_time( 'mysql' );

		$result     = $wpdb->insert(
			$table,
			array_filter( $entity->to_array(), [ $this, 'remove_empty' ] )
		);
		$entity->id = $wpdb->insert_id;
		$this->logger->debug( 'Creating entity.', [ $entity ] );

		// If successful do action
		if ( $result ) {
			do_action( $entity::TABLE . '_added', 'id', $entity->id );
		}

		return $result;

	}

	/**
	 * Deletes all the records for the current repository
	 *
	 * @return int|false
	 * @since 2.0.0
	 */
	public function delete_all() {

		$records = $this->get_all_by();

		if ( $records ) {
			$total = 0;
			foreach ( $records as $record ) {
				$this->delete( 'id', $record->id ) ? $total ++ : null;
			}

			return $total;
		}

		return false;

	}

	/**
	 * Get row by $query_fields array
	 *
	 * @param array $query_fields Key-value pair of fields to query
	 *                            e.g. ['email' => 'john.smith@gmail.com']
	 * @param string $operator Operator to use to join array items. Can be AND or OR.
	 *
	 * @return EntityInterface|null
	 * @since   2.0.0
	 */
	public function get_one_by( array $query_fields, string $operator = 'AND' ) {

		try {
			$where = $this->array_to_where( $query_fields, $operator );
			$table = $this->get_table_name();
			$result = $this->wpdb->get_row( "
			SELECT * FROM $table
			$where
		",
				ARRAY_A );

			if ( $result ) {
				// Return result as Entity specified in repository
				return new $this->repository( $result );
			}

			throw new MapperException("No result found for query", 0, $this->repository);

		} catch ( MapperException $e ) {
			$this->logger->warning( 'Failed to get record.', [ "message" => $e->getMessage(), "query_fields" => $query_fields ] );
		}

		return null;
	}

	/**
	 * Get all results from table
	 *
	 * @param array|null $query_fields Array of columns and their values. The array is converted to
	 *                          a MYSQL WHERE statement as "key = value". If no value is
	 *                          specified it uses "key IS NOT NULL". If array is empty it
	 *                          returns all values in table.
	 *
	 * @return array|object|null
	 * @since   2.0.0
	 */
	public function get_all_by( $query_fields = null ) {

		try {
			$wpdb         = $this->wpdb;
			$table        = $this->get_table_name();
			$query_string = $query_fields ? $this->array_to_where( $query_fields ) : null;

			$results = $wpdb->get_results( "
			SELECT * FROM $table
			$query_string
		",
				ARRAY_A );

			if ( NULL !== $results ) {
				return $this->map_to_class( $results );
			}

			throw new MapperException("No results found for query", 0, $this->repository);

		} catch ( MapperException $e ) {
			$this->logger->warning( 'Failed to get records.', [ "message" => $e->getMessage(), "query_fields" => $query_fields ] );
		}


		return null;
	}

	/**
	 * Returns current repository table name
	 *
	 * @param bool $prefix Whether to return the prefix or not
	 * @return string|false
	 * @throws MapperException
	 * @since   2.0.0
	 */
	public function get_table_name($prefix = true) {

		return $this->get_repository()::get_table_name($prefix);

	}

	/**
	 * Converts an associative array into a query string
	 *
	 * @param array $query_fields Array of key (column) and value pairs
	 * @param string $operator Accepts AND or OR
	 *
	 * @return string
	 * @since 2.0.0
	 */
	private function array_to_where( array $query_fields, string $operator = 'AND' ) {

		array_walk($query_fields, function (&$field, $key) {
			if(empty($key)) {
				$field = sprintf("%s IS NOT NULL", esc_sql($field));
			} else {
				$field = sprintf("%s = '%s'", $key, esc_sql($field));
			}
		});

		return 'WHERE ' . implode( ' ' . $operator . ' ', $query_fields );

	}

	/**
	 * Maps array of current repository objects to instance
	 * of current repository
	 *
	 * @param $results
	 *
	 * @return array
	 * @since   2.0.0
	 */
	private function map_to_class( array $results ) {

		return array_map( function ( $result ) {
			return new $this->repository( $result );
		}, $results );

	}

	/**
	 * Deletes selected record
	 *
	 * @param string $column Column name to search for value
	 * @param string $value
	 *
	 * @return false|int
	 * @since   2.0.0
	 */
	public function delete( string $column, string $value ) {

		$wpdb = $this->wpdb;

		try {
			$deleted = $wpdb->delete(
				$this->get_table_name(),
				[ $column => $value ]
			);
			if ( $deleted ) {
				do_action( $this->get_table_name(false) . '_delete', $column, $value );
			}

			return $deleted;
		} catch ( MapperException $e ) {
			$this->logger->error( 'Unable to delete record.', [ $e->getMessage() ] );
		}

		return false;

	}

	/**
	 * Removes empty values from array
	 *
	 * @param $value
	 *
	 * @return bool
	 * @since 2.0.0
	 */
	private function remove_empty( $value ) {

		return ! is_null( $value ) && $value !== '';

	}
}