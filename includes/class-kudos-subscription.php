<?php

namespace Kudos;

use wpdb;

class Kudos_Subscription {

	use Database_Trait;

	public const TABLE = "kudos_subscriptions";
	/**
	 * @var wpdb
	 */
	protected $wpdb;
	/**
	 * @var string
	 */
	protected $table;

	/**
	 * @param string $transaction_id
	 * @param string $customer_id
	 * @param string $frequency
	 * @param string $value
	 * @param string $currency
	 * @param string $k_subscription_id
	 * @param string $subscription_id
	 * @param $status
	 *
	 * @return bool|false|int
	 * @since   1.1.0
	 */
	public function insert_subscription($transaction_id, $customer_id, $frequency, $value, $currency, $k_subscription_id, $subscription_id, $status) {
		return $this->insert([
			'subscription_created' => current_time('mysql'),
			'value' => $value,
			'currency' => $currency,
			'frequency' => $frequency,
			'subscription_id' => $subscription_id,
			'k_subscription_id' => $k_subscription_id,
			'transaction_id' => $transaction_id,
			'customer_id' => $customer_id,
			'status' => $status
		]);
	}

	/**
	 * Update subscription by email
	 *
	 * @param string $email
	 * @param array $array
	 *
	 * @return false|int
	 */
	public function update_subscription($email, $array) {

		return $this->update($array, ['email' => $email]);
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

		return $this->wpdb->get_results(
			"SELECT 
       					$this->table.*,
       					$donor_table.*
			FROM $this->table
			LEFT JOIN $donor_table on $this->table.customer_id = $donor_table.customer_id
			$search_custom_vars
			", ARRAY_A
		);
	}
}