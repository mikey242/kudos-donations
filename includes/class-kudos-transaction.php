<?php

namespace Kudos;

use wpdb;

class Kudos_Transaction {

	/**
	 * @var wpdb
	 */
	private $wpdb;
	const TABLE = "kudos_transactions";

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * @param string $order_id
	 * @param string $value
	 * @param string $currency
	 * @param string $status
	 * @param string $sequence_type
	 * @param string $email
	 * @param string $name
	 */
	public function create_transaction($order_id, $value, $currency, $status, $sequence_type,  $email=null, $name=null) {
		$this->wpdb->insert(
			$this->wpdb->prefix . self::TABLE,
			[
				'time' => current_time('mysql'),
				'name' => $name,
				'email' => $email,
				'value' => $value,
				'currency' => $currency,
				'status' => $status,
				'mode' => get_option('_kudos_mollie_api_mode'),
				'sequence_type' => $sequence_type,
				'order_id' => $order_id
			]
		);
	}

	/**
	 * @param string $order_id
	 * @param string $transaction_id
	 * @param string $status
	 * @param string $method
	 */
	public function update_transaction($order_id, $transaction_id, $status, $method) {
		$this->wpdb->update(
			$this->wpdb->prefix . self::TABLE,
			[
				'status' => $status,
				'transaction_id' => $transaction_id,
				'method' => $method
			],
			[
				'order_id' => $order_id
			]
		);
	}

	/**
	 * @param $order_id
	 * @param array $fields
	 *
	 * @return array|object|void|null
	 */
	public function get_transaction($order_id, array $fields=['*']) {

		$wpdb = $this->wpdb;
		$table = $this->wpdb->prefix . self::TABLE;
		$columns = implode(', ', $fields);

		return $wpdb->get_row( $wpdb->prepare( "
			SELECT $columns FROM $table WHERE order_id = '%s'
		", $order_id ) );
	}
}