<?php

namespace Kudos\Service;

use Kudos\Entity\AbstractEntity;
use wpdb;

class MapperService {

	/**
	 * @var wpdb
	 */
	protected $wpdb;
	/**
	 * @var AbstractEntity
	 */
	protected $repository;
	/**
	 * @var LoggerService
	 */
	private $logger;

	/**
	 * Entity object constructor.
	 *
	 * @param AbstractEntity|string|null $repository
	 *
	 * @since   2.0.0
	 */
	public function __construct( $repository = null ) {

		global $wpdb;
		$this->wpdb = $wpdb;
		$this->set_repository( $repository );
		$this->logger = new LoggerService();

	}

	/**
	 * Specify the repository to use
	 *
	 * @param AbstractEntity|string $class
	 *
	 * @since 2.0.0
	 */
	public function set_repository( $class ) {

		$this->repository = $class;

	}

	/**
	 * Commit Entity to database
	 *
	 * @param AbstractEntity $entity
	 *
	 * @return bool|false|int
	 * @since   2.0.0
	 */
	public function save( $entity ) {

		$wpdb                 = $this->wpdb;
		$table                = $entity::get_table_name();
		$entity->last_updated = current_time( 'mysql' );

		// If we have an id, then update row
		if ( $entity->id ) {
			$this->logger->debug( 'Updating entity.', [ $entity ] );

			return $wpdb->update(
				$table,
				array_filter( $entity->to_array(), [ $this, 'remove_empty' ] ),
				[ 'id' => $entity->id ]
			);
		}

		// Otherwise insert new row
		$entity->created = current_time( 'mysql' );
		$this->logger->debug( 'Creating entity.', [ $entity ] );

		return $wpdb->insert(
			$table,
			array_filter( $entity->toArray(), [ $this, 'remove_empty' ] )
		);

	}

	/**
	 * Deletes all the records for the current repository
	 *
	 * @return int|false
	 * @since 2.0.0
	 */
	public function delete_all() {

		if ( null === $this->repository ) {
			return null;
		}

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
	 * Get all results from table
	 *
	 * @param array|null $query Array of columns and their values. The array is converted to
	 *                          a MYSQL WHERE statement as "key = value". If no value is
	 *                          specified it uses "key IS NOT NULL". If array is empty it
	 *                          returns all values in table.
	 * @param string $format Specifies the return format. Defaults to OBJECT but can also
	 *                          be ARRAY_A.
	 *
	 * @return array|object|null
	 * @since   2.0.0
	 */
	public function get_all_by( $query = null, $format = OBJECT ) {

		if ( null === $this->repository ) {
			return null;
		}

		$wpdb         = $this->wpdb;
		$table        = $this->get_table_name();
		$query_string = $query ? $this->array_to_where( $query ) : null;

		$results = $wpdb->get_results( "
			SELECT * FROM $table
			$query_string
		",
			$format );

		if ( $results ) {
			if ( $format === 'OBJECT' ) {
				return $this->map_to_class( $results );
			}

			return $results;
		}

		return null;
	}

	/**
	 * Returns current repository table name
	 *
	 * @return string|false
	 * @since   2.0.0
	 */
	public function get_table_name() {

		if ( null === $this->repository ) {
			return false;
		}

		return $this->repository::get_table_name();
	}

	/**
	 * Converts an associative array into a query string
	 *
	 * @param array $query_fields
	 * @param string $operator Accepts AND or OR
	 *
	 * @return string
	 * @since 2.0.0
	 */
	private function array_to_where( array $query_fields, string $operator = 'AND' ) {

		$wpdb  = $this->wpdb;
		$array = [];
		foreach ( $query_fields as $key => $field ) {
			if ( empty( $key ) ) {
				array_push( $array,
					"$field IS NOT NULL"
				);
			} else {
				array_push( $array,
					$wpdb->prepare(
						"$key = %s",
						$field
					) );
			}
		}

		return 'WHERE ' . implode( ' ' . $operator . ' ', $array );

	}

	/**
	 * Maps array of standard objects to Entity class
	 *
	 * @param $results
	 *
	 * @return array
	 * @since   2.0.0
	 */
	private function map_to_class( $results ) {

		$array = [];
		foreach ( $results as $result ) {
			$array[] = new $this->repository( $result );
		}

		return $array;
	}

	/**
	 * Deletes selected record
	 *
	 * @param string $column
	 * @param string $value
	 *
	 * @return false|int
	 * @since   2.0.0
	 */
	public function delete( string $column, string $value ) {

		$wpdb = $this->wpdb;

		return $wpdb->delete(
			$this->get_table_name(),
			[ $column => $value ]
		);

	}

	/**
	 * Get row by $query_fields array
	 *
	 * @param array $query_fields // Key-value pair of fields to query e.g. ['email' => 'john.smith@gmail.com']
	 * @param string $operator // AND or OR
	 *
	 * @return AbstractEntity|null
	 * @since   2.0.0
	 */
	public function get_one_by( array $query_fields, string $operator = 'AND' ) {

		if ( null === $this->repository ) {
			return null;
		}

		$wpdb  = $this->wpdb;
		$where = $this->array_to_where( $query_fields, $operator );
		$table = $this->repository::get_table_name();

		$result = $wpdb->get_row( "
			SELECT * FROM $table
			$where
		" );

		if ( $result ) {
			// Return result as Entity specified in repository
			return new $this->repository( $result );
		}

		return null;
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