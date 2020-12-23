<?php

namespace Kudos\Entity;

use Kudos\Service\MapperService;

class DonorEntity extends AbstractEntity {

	/**
	 * Table name without prefix
	 *
	 * @var string
	 */
	protected const TABLE = 'kudos_donors';
	/**
	 * Email address of donor. Used as a unique identifier
	 *
	 * @var string
	 */
	public $email;
	/**
	 * Donor's name
	 *
	 * @var string
	 */
	public $name;
	/**
	 * Address: Street name
	 *
	 * @var string
	 */
	public $street;
	/**
	 * * Address: Postcode
	 *
	 * @var string
	 */
	public $postcode;
	/**
	 * * Address: City
	 *
	 * @var string
	 */
	public $city;
	/**
	 * Address: Country
	 *
	 * @var string
	 */
	public $country;
	/**
	 * The customer id used by mollie
	 *
	 * @var string
	 */
	public $customer_id;

	/**
	 * Gets all transactions for current user
	 *
	 * @return array|null
	 */
	public function get_transactions(): ?array {

		$mapper = new MapperService( TransactionEntity::class );

		return $mapper->get_all_by( [ 'customer_id' => $this->customer_id ] );

	}

}
