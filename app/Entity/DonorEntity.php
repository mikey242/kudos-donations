<?php

namespace Kudos\Entity;

use DateTime;
use Kudos\Service\MapperService;

class DonorEntity extends AbstractEntity {

	/**
	 * Table name without prefix
	 * @var string
	 */
	protected const TABLE = "kudos_donors";
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
	 * DonorEntity constructor
	 *
	 * @param $atts
	 *
	 * @since   2.0.0
	 */
	public function __construct( $atts = null ) {

		parent::__construct( $atts );

	}

	/**
	 * Gets all transactions for current user
	 *
	 * @return array|null
	 */
	public function get_transactions() {

		$mapper = new MapperService( TransactionEntity::class );

		return $mapper->get_all_by( [ 'customer_id' => $this->customer_id ] );

	}

}