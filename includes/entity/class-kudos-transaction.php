<?php

namespace Kudos\Entity;

use DateTime;
use Kudos\Entity;
use Kudos\Kudos_Mapper;

class Transaction extends Entity {

	public const TABLE = "kudos_transactions";

	/**
	 * @var DateTime
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
	public $status;
	/**
	 * @var string
	 */
	public $method;
	/**
	 * @var string
	 */
	public $mode;
	/**
	 * @var string
	 */
	public $sequence_type;
	/**
	 * @var string
	 */
	public $transaction_id;
	/**
	 * @var string
	 */
	public $order_id;
	/**
	 * @var string
	 */
	public $customer_id;
	/**
	 * @var string
	 */
	public $subscription_id;
	/**
	 * @var string
	 */
	public $donation_label;
	/**
	 * @var string
	 */
	public $refunds;
	/**
	 * @var DateTime
	 */
	public $last_updated;

	/**
	 * Add donor_created
	 *
	 * @param null|array $atts
	 * @since   2.0.0
	 */
	public function __construct($atts=null) {
		parent::__construct($atts);
	}

	/**
	 * Gets donor associated with transaction
	 *
	 * @return Donor|Entity|null
	 * @since   2.0.0
	 */
	public function get_donor() {
		$mapper = new Kudos_Mapper(Donor::class);
		return $mapper->get_by(['customer_id' => $this->customer_id]);
	}

	/**
	 * Returns unserialized array of refund data
	 *
	 * @return mixed
	 * @since   2.0.0
	 * @return array|false
	 */
	public function get_refunds() {

		$refunds = $this->refunds;
		if(is_serialized($refunds)) {
			return unserialize($refunds);
		}
		return false;
	}
}