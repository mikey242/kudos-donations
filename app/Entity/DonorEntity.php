<?php
/**
 * Donor entity.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

namespace Kudos\Entity;

class DonorEntity extends AbstractEntity {

	/**
	 * Table name without prefix
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
}
