<?php

namespace Kudos\Entity;

use DateTime;
use Kudos\Entity;
use Kudos\Kudos_Mapper;

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
	 * UNMAPPED
	 * @var Donor
	 */
	public $donor;

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
	 * Gets donor associated with transaction
	 *
	 * @return Donor|Entity|null
	 * @since   2.0.0
	 */
	public function get_donor() {

		if($this->donor) {
			return $this->donor;
		}

		$mapper = new Kudos_Mapper(Donor::class);
		return $mapper->get_by(['customer_id' => $this->customer_id]);
	}
}