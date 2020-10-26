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
			try {
				$this->set_repository( $repository );
			} catch ( ReflectionException | MapperException $e ) {
				$this->logger->error( "Could not set repository", [ "message" => $e->getMessage() ] );
			}
		}
	}

	/**
	 * Specify the repository to use
	 *
	 * @param string $class
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
		} catch (ReflectionException $e) {
			$this->logger->error($e->getMessage());
		}

	}

	/**
	 * Gets the current repository
	 *
	 * @return AbstractEntity
	 * @since 2.0.5
	 */
	public function get_repository() {

		try {
			if ( NULL === $this->repository ) {
				throw new MapperException( "No repository specified" );
			}
		} catch ( MapperException $e ) {
			$this->logger->warning( 'Failed to get repository.', [ "message" => $e->getMessage() ] );
		}

		return $this->repository;

	}

	/**
	 * Commit Entity to database
	 *
	 * @param EntityInterface $entity
	 *
	 * @param bool $ignore_null Whether or not to remove NULL or empty fields from
	 *                          the save query.
	 *
	 * @return false|int Returns the id of the record if successful
	 *                  and false if not
	 * @since   2.0.0
	 */
	public function save( EntityInterface $entity, bool $ignore_null = true ) {

		$entity->last_updated = date( 'Y-m-d H:i:s', time() );

		// If we have an id, then update row
		if ( $entity->id ) {
			return $this->update_record( $entity, $ignore_null );
		}

		// Otherwise create new record
		return $this->add_record( $entity );

	}

	/**
	 * Adds new record to the database
	 *
	 * @param $entity
	 *
	 * @return false|int Returns the id of the record if successful
	 *                  and false if not
	 */
	private function add_record( $entity ) {

		$wpdb       = $this->wpdb;
		$table_name = $entity::get_table_name();

		// Otherwise insert new row
		$entity->created = date( 'Y-m-d H:i:s', time() );

		$result     = $wpdb->insert(
			$table_name,
			$entity->to_array()
		);
		$id         = $wpdb->insert_id;
		$entity->id = $id;
		$this->logger->debug( 'Creating entity.', [ $entity ] );

		// If successful do action
		if ( $result ) {
			do_action( $entity::get_table_name( false ) . '_added', 'id', $id );

			return $id;
		}

		return $result;

	}

	/**
	 * Updates existing record
	 *
	 * @param EntityInterface $entity // An instance of EntityInterface
	 * @param bool $ignore_null
	 *
	 * @return false|int Returns the id of the record if successful
	 *                  and false if not
	 */
	private function update_record( EntityInterface $entity, bool $ignore_null ) {

		$wpdb       = $this->wpdb;
		$table_name = $entity::get_table_name();
		$id         = $entity->id;

		$this->logger->debug( 'Updating entity.', [ $entity ] );

		$result = $wpdb->update(
			$table_name,
			$ignore_null ? array_filter( $entity->to_array(), [$this, 'remove_empty'] ) : $entity->to_array(),
			[ 'id' => $id ]
		);

		if ( $result ) {
			do_action( $entity::get_table_name( false ) . '_updated', 'id', $id );

			return $id;
		}

		return $result;
	}

	/**
	 * Deletes all the records for the current repository
	 *
	 * @return bool|int Returns the number of records deleted if successful
	 *                  and false if not
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
	 * @return AbstractEntity|null
	 * @since   2.0.0
	 */
	public function get_one_by( array $query_fields, string $operator = 'AND' ) {


		$where  = $this->array_to_where( $query_fields, $operator );
		$table  = $this->get_table_name();
		$result = $this->wpdb->get_row( "
			SELECT * FROM $table
			$where",
			ARRAY_A );

		if ( $result ) {
			// Return result as Entity specified in repository
			return new $this->repository( $result );
		}

		return null;
	}

	/**
	 * Get all results from table
	 *
	 * @param array|null $query_fields Array of columns and their values. The array is converted to
	 *                                 a MYSQL WHERE statement as "key = value". If no value is
	 *                                 specified it uses "key IS NOT NULL". If array is empty it
	 *                                 returns all values in table.
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

			if ( ! empty( $results ) ) {
				return $this->map_to_class( $results );
			}

			throw new MapperException( "No results found for query", 0, $this->repository );

		} catch ( MapperException $e ) {
			$this->logger->warning( 'Failed to get records.',
				[ "message" => $e->getMessage(), "query_fields" => $query_fields ] );
		}


		return null;
	}

	/**
	 * Returns current repository table name
	 *
	 * @param bool $prefix Whether to return the prefix or not
	 *
	 * @return string|false
	 * @since   2.0.0
	 */
	public function get_table_name( $prefix = true ) {

		return $this->get_repository()::get_table_name( $prefix );

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

		array_walk( $query_fields,
			function ( &$field, $key ) {
				if ( empty( $key ) ) {
					$field = sprintf( "%s IS NOT NULL", esc_sql( $field ) );
				} else {
					$field = sprintf( "%s = '%s'", $key, esc_sql( $field ) );
				}
			} );

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
		},
			$results );

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

		$deleted = $wpdb->delete(
			$this->get_table_name(),
			[ $column => $value ]
		);

		if ( $deleted ) {
			do_action( $this->get_table_name( false ) . '_delete', $column, $value );
		}

		return $deleted;

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