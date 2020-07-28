<?php

namespace Kudos\Entity;

use DateTime;
use Kudos\Entity;

class Transaction extends Entity {

	public const TABLE = "kudos_transactions";

	/**
	 * Add donor_created
	 *
	 * @param $atts
	 * @since   2.0.0
	 */
	public function __construct($atts=null) {
		parent::__construct($atts);
		$this->fields['transaction_created'] = current_time('mysql');
	}

	/**
	 * Gets donor associated with transaction
	 *
	 * @return Donor
	 * @since   2.0.0
	 */
	public function get_donor() {
		$donor = new Donor();
		$donor->get_by(['customer_id' => $this->fields['customer_id']]);
		return $donor;
	}

	/**
	 * Returns unserialized array of refund data
	 *
	 * @return mixed
	 * @since   2.0.0
	 */
	public function get_refunds() {

		$refunds = $this->fields['refunds'];
		if(is_serialized($refunds)) {
			return unserialize($refunds);
		}
		return false;
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
		$donor_table = $wpdb->prefix . Donor::TABLE;
		$transaction_table = $wpdb->prefix . self::TABLE;

		$query = "SELECT 
       			  	t.*,
					d.*
				  FROM $transaction_table AS t
				  LEFT JOIN $donor_table as d ON t.customer_id = d.customer_id
				  $search_custom_vars
		";

		return $wpdb->get_results($query, ARRAY_A);
	}
}