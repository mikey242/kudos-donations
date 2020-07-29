<?php

namespace Kudos\Entity;

use DateTime;
use Kudos\Entity;

class Subscription extends Entity {

	/**
	 * @var DateTime;
	 */
	public $created;
	/**
	 * @var int
	 */
	public $value;
	/**
	 * @var string
	 */
	public $currency;
	/**
	 * @var string
	 */
	public $frequency;
	/**
	 * @var int
	 */
	public $years;
	/**
	 * @var string
	 */
	public $status;
	/**
	 * @var string
	 */
	public $customer_id;
	/**
	 * @var string
	 */
	public $transaction_id;
	/**
	 * @var string
	 */
	public $subscription_id;
	/**
	 * @var DateTime
	 */
	public $last_updated;

	/**
	 * Table name without prefix
	 * @var string
	 */
	public const TABLE = "kudos_subscriptions";

	/**
	 * Subscription constructor.
	 *
	 * @param null|array $atts
	 */
	public function __construct($atts=null) {
		parent::__construct($atts);
	}

	/**
	 * Gets data for table view in admin
	 *
	 * @param null $search_custom_vars
	 *
	 * @return array|object|null
	 */
	public static function get_table_data($search_custom_vars) {

		global $wpdb;
		$donor_table = Donor::getTableName();
		$subscription_table = $wpdb->prefix . self::TABLE;

		return $wpdb->get_results(
			"SELECT 
       					$subscription_table.*,
       					$donor_table.*
			FROM $subscription_table
			LEFT JOIN $donor_table on $subscription_table.customer_id = $donor_table.customer_id
			$search_custom_vars
			", ARRAY_A
		);
	}
}