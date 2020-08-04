<?php

namespace Kudos\Service;

use Kudos\Entity;
use wpdb;

class Mapper {

	/**
	 * @var wpdb
	 */
	protected $wpdb;
	/**
	 * @var Entity
	 */
	protected $repository;
	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * Entity object constructor.
	 *
	 * @param Entity|string|null $repository
	 * @since   2.0.0
	 */
	public function __construct($repository=null) {

		global $wpdb;
		$this->wpdb = $wpdb;
		$this->set_repository($repository);
		$this->logger = new Logger();

	}

	/**
	 * Converts an associative array into a query string
	 *
	 * @param $query_fields
	 * @param string $operator Accepts AND or OR
	 * @return string
	 * @since 2.0.0
	 */
	private function array_to_where($query_fields, $operator='AND') {

		$wpdb = $this->wpdb;
		$array = [];
		foreach ($query_fields as $key=>$field) {
			array_push($array, $wpdb->prepare(
				"$key = %s", $field
			));
		}

		return 'WHERE ' . implode(' ' . $operator . ' ', $array);

	}

	/**
	 * Specify the repository to use
	 *
	 * @param Entity|string $class
	 * @since 2.0.0
	 */
	public function set_repository($class) {

		$this->repository = $class;

	}

	/**
	 * Commit entity to database
	 *
	 * @param Entity $entity
	 * @return bool|false|int
	 * @since   2.0.0
	 */
	public function save($entity) {

		$wpdb = $this->wpdb;
		$table = $entity::getTableName();
		$entity->last_updated = current_time('mysql');

		// If we have an id, then update row
		if($entity->id) {
			return $wpdb->update(
				$table,
				(array) $entity,
				['id' => $entity->id]
			);
		}

		// Otherwise insert new row
		$entity->created = current_time('mysql');
		return $wpdb->insert(
			$table,
			(array) $entity
		);

	}

	/**
	 * Deletes selected record
	 *
	 * @param string $column
	 * @param $value
	 * @return false|int
	 * @since   2.0.0
	 */
	public function delete($column, $value) {

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
	 * @return Entity|null
	 * @since   2.0.0
	 */
	public function get_one_by($query_fields, $operator='AND') {

		if(NULL === $this->repository) {
			return null;
		}

		$wpdb = $this->wpdb;
		$where = $this->array_to_where($query_fields, $operator);
		$table = $this->repository::getTableName();

		$result = $wpdb->get_row("
			SELECT * FROM $table
			$where
		");

		if($result) {
			// Return result as Entity specified in repository
			return new $this->repository($result);
		}

		return null;
	}

	/**
	 * Get all results from table
	 *
	 * @param $query
	 * @param string $format
	 * @return array|null
	 * @since   2.0.0
	 */
	public function get_all_by($query=null, $format=OBJECT) {

		if(NULL === $this->repository) {
			return null;
		}

		$wpdb = $this->wpdb;
		$table = $this->get_table_name();
		$query_string = $query ? $this->array_to_where($query) : null;

		$results = $wpdb->get_results("
			SELECT * FROM $table
			$query_string
		", $format);

		if($results) {
			if($format === 'OBJECT') {
				return  $this->map_to_class($results);
			}

			return $results;
		}

		return null;
	}

	/**
	 * Maps array of standard objects to Entity class
	 *
	 * @param $results
	 * @return array
	 * @since   2.0.0
	 */
	private function map_to_class($results) {

		$array = [];
		foreach ( $results as $result ) {
			$array[] = new $this->repository($result);
		}

		return $array;
	}

	/**
	 * Returns current repository table name
	 *
	 * @return string|false
	 * @since   2.0.0
	 */
	public function get_table_name() {

		if(NULL === $this->repository) {
			return false;
		}

		return $this->repository::getTableName();
	}
}