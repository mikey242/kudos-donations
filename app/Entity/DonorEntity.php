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
	 * API Mode used to create donor.
	 *
	 * @var string
	 */
	public $mode;
	/**
	 * Donor's name
	 *
	 * @var string
	 */
	public $name;
	/**
	 * Business name
	 *
	 * @var string
	 */
	public $business_name;
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
	 * The customer id used by vendor
	 *
	 * @var string
	 */
	public $customer_id;

	/**
	 * Gets all transactions for current user
	 *
	 * @return TransactionEntity|null
	 */
	public function get_transactions(): ?TransactionEntity {

		$mapper = new MapperService( TransactionEntity::class );
		/** @var TransactionEntity $transactions */
		$transactions = $mapper->get_all_by( [ 'customer_id' => $this->customer_id ] );

		return $transactions ?? null;

	}

}
