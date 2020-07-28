<?php

namespace Kudos;

use wpdb;

class Entity {

	/**
	 * @var array
	 */
	public $fields = [
		'id' => ''
	];
	/**
	 * @var wpdb
	 */
	protected $wpdb;

	/**
	 * Entity object constructor.
	 *
	 * @param null|array $atts
	 *
	 * @since   2.0.0
	 */
	public function __construct($atts=null) {
		
		global $wpdb;
		$columns = $wpdb->get_col("DESC {$this::getTableName()}", 0);
		$this->fields = array_fill_keys($columns, '');
		
		$this->wpdb = $wpdb;

		if(null !== $atts) {
			$this->set_fields($atts);
		}
	}

	/**
	 * Set class properties based on array values
	 *
	 * @param $atts
	 * @since   2.0.0
	 */
	public function set_fields($atts) {
		foreach ($atts as $property => $value) {
			if(array_key_exists($property, $this->fields)) {
				$this->fields[$property] = $value;
			}
		}
	}

	/**
	 * Returns the table name associated with entity
	 *
	 * @return string
	 * @since   2.0.0
	 */
	public static function getTableName() {
		global $wpdb;
		return $wpdb->prefix . static::TABLE;
	}

	/**
	 * Converts an associative array into a query string
	 *
	 * @param $query_fields
	 * @param string $operator Accepts AND or OR
	 * @return string
	 */
	private function array_to_query($query_fields, $operator='AND') {
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
	 * Commit entity to database
	 *
	 * @since   2.0.0
	 */
	public function save() {

		$wpdb = $this->wpdb;

		$table = $this->getTableName();

		// If we have an id, then update row
		if($this->fields['id']) {
			return $wpdb->update(
				$table,
				(array) $this->fields,
				['id' => $this->fields['id']]
			);
		}

		// Otherwise insert new row
		return $wpdb->insert(
			$table,
			(array) $this->fields
		);
	}

	/**
	 * Get row by $query_fields array
	 *
	 * @param array $query_fields // Key-value pair of fields to query e.g. ['email' => 'john.smith@gmail.com']
	 * @param string $operator
	 *
	 * @since   2.0.0
	 */
	public function get_by($query_fields, $operator='AND') {

		$wpdb = $this->wpdb;
		$where = $this->array_to_query($query_fields, $operator);
		$table = $this->getTableName();

		$result = $wpdb->get_row("
			SELECT * FROM $table
			$where
		");

		if($result) {
			$this->set_fields($result);
		}

	}

	/**
	 * Get all results from table
	 *
	 * @param $query
	 * @param string $format
	 * @return array|object
	 * @since   2.0.0
	 */
	public function get_all($query=null, $format=ARRAY_A) {

		global $wpdb;
		$table = $this->getTableName();
		$query_string = $query ? $this->array_to_query($query) : null;

		$results = $wpdb->get_results("
			SELECT * FROM $table
			$query_string
		", $format);

		return $this->map_to_class($results);
	}

	/**
	 * Maps array of standard objects to Entity class
	 *
	 * @param $results
	 * @return array
	 */
	private function map_to_class($results) {

		$array = [];
		foreach ( $results as $result ) {
			$array[] = new $this($result);
		}

		return $array;
	}

	/**
	 * Gets data for table view in admin
	 *
	 * @param null $search_custom_vars
	 * @return array|object|null
	 * @since   2.0.0
	 */
	public static function get_table_data($search_custom_vars) {

		global $wpdb;
		$table = self::getTableName();

		return $wpdb->get_results("
			SELECT * FROM $table
			$search_custom_vars
		", ARRAY_A);

	}
}