<?php

namespace Kudos;

use wpdb;

class Database_Object {

	/**
	 * @var wpdb
	 */
	protected $wpdb;
	/**
	 * @var string
	 */
	protected $table;

	/**
	 * Database object constructor.
	 *
	 * @since   1.1.0
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->table = $wpdb->prefix . static::TABLE;
	}

	/**
	 * Update table using array for fields
	 *
	 * @param $fields
	 * @param $where
	 *
	 * @return false|int
	 *
	 * @since   1.1.0
	 */
	public function update($fields, $where) {
		return $this->wpdb->update(
			$this->table,
			$fields, $where
		);
	}

	/**
	 * Insert row
	 *
	 * @param array $fields
	 *
	 * @return bool|false|int
	 * @since   1.1.0
	 */
	public function insert($fields) {
		return $this->wpdb->insert(
			$this->table, $fields
		);
	}

	/**
	 * Get row by $query_fields array
	 *
	 * @param array $query_fields // Key-value pair of fields to query e.g. ['email' => 'john.smith@gmail.com']
	 * @param array $return_fields // Fields returned, defaults to all e.g. ['name', 'email']
	 * @param null|string $before_where Additional query string
	 * @param null $after_where Additional query string
	 *
	 * @return object|null
	 *
	 * @since   1.1.0
	 */
	public function get_by($query_fields, $return_fields=["*"], $before_where=null, $after_where=null) {

		$wpdb = $this->wpdb;
		$columns = implode(', ', $return_fields);
		$array = [];
		foreach ($query_fields as $key=>$field) {
			array_push($array, $wpdb->prepare(
				"$key = %s", $field
			));
		}

		$where = 'WHERE ' . implode(' AND ', $array);

		return $wpdb->get_row("
			SELECT $columns FROM $this->table
		    $before_where
			$where
			$after_where
		");
	}

	/**
	 * Get all results from table
	 *
	 * @param null|string $query Additional query string
	 *
	 * @param string $format
	 *
	 * @return array|object|null
	 *
	 * @since   1.1.0
	 */
	public function get_all($query=null, $format=OBJECT) {
		$wpdb = $this->wpdb;
		$table = $this->table;

		return $wpdb->get_results("
			SELECT * FROM $table
			$query
		", $format);
	}
}