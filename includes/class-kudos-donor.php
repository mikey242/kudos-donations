<?php

namespace Kudos;

use wpdb;

class Kudos_Donor {

	use Database_Trait;

	public const TABLE = "kudos_donors";
	/**
	 * @var wpdb
	 */
	protected $wpdb;
	/**
	 * @var string
	 */
	protected $table;

	/**
	 * @param string $email
	 * @param string $customer_id
	 * @param string $name
	 * @param string|null $street
	 * @param string|null $postcode
	 * @param string|null $city
	 * @param string|null $country
	 *
	 * @return bool|false|int
	 * @since   1.1.0
	 */
	public function insert_donor($email, $customer_id, $name=null, $street=null, $postcode=null, $city=null, $country=null) {

		return $this->insert([
			'donor_created' => current_time('mysql'),
			'email' => $email,
			'name' => $name,
			'street' => $street,
			'postcode' => $postcode,
			'city' => $city,
			'country' => $country,
			'customer_id' => $customer_id,
		]);
	}

	/**
	 * Update donor by email
	 *
	 * @param string $email
	 * @param array $array
	 *
	 * @return false|int
	 *
	 * * @since   1.1.0
	 */
	public function update_donor($email, $array) {

		return $this->update($array, ['email' => $email]);
	}
}