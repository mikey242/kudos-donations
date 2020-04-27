<?php

namespace Kudos\Transactions;

use wpdb;

class Transaction {

	/**
	 * @var wpdb
	 */
	private $wpdb;
	public const TABLE = "kudos_transactions";

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * @param string $order_id
	 * @param string $value
	 * @param string $email
	 * @param string $name
	 */
	public function create_record($order_id, $value, $email=null, $name=null) {
		$this->wpdb->insert(
			$this->wpdb->prefix . self::TABLE,
			[
				'time' => current_time('mysql'),
				'name' => $name,
				'email' => $email,
				'value' => $value,
				'status' => 'open',
				'mode' => get_option('_kudos_mollie_api_mode'),
				'order_id' => $order_id
			]
		);
	}

	/**
	 * @param string $order_id
	 * @param string $transaction_id
	 * @param string $status
	 */
	public function update_record($order_id, $transaction_id, $status) {
		$this->wpdb->update(
			$this->wpdb->prefix . self::TABLE,
			[
				'status' => $status,
				'transaction_id' => $transaction_id
			],
			[
				'order_id' => $order_id
			]
		);
	}

	public function get_transaction($order_id) {
		$wpdb = $this->wpdb;
		$table = $this->wpdb->prefix . self::TABLE;
		return $wpdb->get_row( sprintf( "
			SELECT * FROM $table WHERE order_id = '%s'
		", esc_sql($order_id) ) );
	}
}