<?php

namespace Kudos\Entity;

use DateTime;
use Kudos\Entity;
use Kudos\Service\Logger;
use Kudos\Service\Mapper;
use Throwable;

class Donor extends Entity {

	public const TABLE = "kudos_donors";
	/**
	 * @var DateTime
	 */
	public $created;
	/**
	 * @var string
	 */
	public $email;
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $street;
	/**
	 * @var string
	 */
	public $postcode;
	/**
	 * @var string
	 */
	public $city;
	/**
	 * @var string
	 */
	public $country;
	/**
	 * @var string
	 */
	public $customer_id;
	/**
	 * @var DateTime
	 */
	public $last_updated;


	/**
	 * Add donor_created
	 *
	 * @param $atts
	 * @since   2.0.0
	 */
	public function __construct($atts=null) {
		parent::__construct($atts);
	}

	/**
	 * Gets all transactions for current user
	 *
	 * @return array|null
	 */
	public function get_transactions() {
		$mapper = new Mapper(Transaction::class);
		return $mapper->get_all_by([ 'customer_id' => $this->customer_id]);
	}

	public function __toString() {
		return $this->customer_id;
	}

}