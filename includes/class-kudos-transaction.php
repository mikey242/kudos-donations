<?php

namespace Kudos;

use wpdb;

class Kudos_Transaction {

	use Database_Trait;

	public const TABLE = "kudos_transactions";
	/**
	 * @var wpdb
	 */
	protected $wpdb;
	/**
	 * @var string
	 */
	protected $table;

	/**
	 * Insert new transaction
	 *
	 * @param array $fields
	 *
	 * @return bool|false|int
	 */
	public function insert_transaction($fields) {

		$fields['transaction_created'] = current_time('mysql');

		return $this->insert($fields);
	}

	/**
	 * Update transaction by order_id
	 *
	 * @param string $order_id
	 * @param array $array
	 *
	 * @return false|int
	 * @since   1.1.0
	 *
	 */
	public function update_transaction($order_id, $array) {

		return $this->update($array, ['order_id' => $order_id]);
	}

	/**
	 * Returns all transactions
	 *
	 * @return object|null
	 * @since   1.1.0
	 */
	public function get_transactions() {

		$wpdb = $this->wpdb;
		$transaction_table = $this->table;
		$donor_table = $wpdb->prefix . Kudos_Donor::TABLE;
		$query = "LEFT JOIN $donor_table ON $transaction_table.customer_id = $donor_table.customer_id";
		return $this->get_all($query);
	}

	/**
	 * Get one transaction by $query_fields array
	 *
	 * @param array $query_fields // Key-value pair of fields to query e.g. ['email' => 'john.smith@gmail.com']
	 * @param array $return_fields // Fields returned, defaults to all e.g. ['name', 'email']
	 *
	 * @return object|null
	 */
	public function get_transaction_by($query_fields, $return_fields=["*"]) {

		$transaction_table = $this->table;
		$donor_table = $this->wpdb->prefix . Kudos_Donor::TABLE;
		$before_where = "LEFT JOIN $donor_table ON $transaction_table.customer_id = $donor_table.customer_id";

		return $this->get_by($query_fields, $return_fields, $before_where);
	}

	/**
	 * Gets data for table view in admin
	 *
	 * @param null $search_custom_vars
	 *
	 * @return array|object|null
	 */
	public function get_table_data($search_custom_vars) {

		$wpdb = $this->wpdb;
		$donor_table = $wpdb->prefix . Kudos_Donor::TABLE;

		$query = "SELECT 
       			  	t.*,
					d.*
				  FROM $this->table AS t
				  LEFT JOIN $donor_table as d ON t.customer_id = d.customer_id
				  $search_custom_vars
		";

		return $wpdb->get_results($query, ARRAY_A);
	}
}