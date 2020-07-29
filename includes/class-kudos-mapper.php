<?php

namespace Kudos;

use wpdb;

class Kudos_Mapper {

	/**
	 * @var wpdb
	 */
	protected $wpdb;
	/**
	 * @var Entity
	 */
	protected $repository;
	/**
	 * @var Kudos_Logger
	 */
	private $logger;

	/**
	 * Entity object constructor.
	 *
	 * @param Entity|string|null $repository
	 *
	 * @since   2.0.0
	 */
	public function __construct($repository=null) {

		global $wpdb;
		$this->wpdb = $wpdb;
		$this->set_repository($repository);
		$this->logger = new Kudos_Logger();
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
	 * Get row by $query_fields array
	 *
	 * @param array $query_fields // Key-value pair of fields to query e.g. ['email' => 'john.smith@gmail.com']
	 * @param string $operator // AND or OR
	 * @return Entity|null
	 * @since   2.0.0
	 */
	public function get_by($query_fields, $operator='AND') {

		if(NULL === $this->repository) {
			$this->logger->debug('Unable to get results as repository not set', [$query_fields]);
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
	public function get_all($query=null, $format=ARRAY_A) {

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
			return  $this->map_to_class($results);
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

	/**
	 * Gets data for table view in admin
	 *
	 * @param string|null $search_custom_vars
	 * @param array $join \\ e.g [wp_table_name => join_field]
	 * @return array|null|false
	 * @since   2.0.0
	 */
	public function get_table_data($search_custom_vars, $join=[]) {

		if(NULL === $this->repository) {
			return false;
		}

		$wpdb = $this->wpdb;
		$table = $this->get_table_name();

		if($join) {
			$join_esc = esc_sql($join);
			$join_table = $join_esc[0];
			$join_field = $join_esc[1];
			$search_custom_vars = " LEFT JOIN $join_table on $join_table.$join_field = $table.$join_field " . $search_custom_vars;
		}

		return $wpdb->get_results("
			SELECT *
			FROM $table
			$search_custom_vars
		", ARRAY_A);

	}
}