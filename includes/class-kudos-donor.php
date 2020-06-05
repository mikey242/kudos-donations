<?php

namespace Kudos;

use wpdb;

class Kudos_Donor {

	/**
	 * @var wpdb
	 */
	private $wpdb;
	const TABLE = "kudos_donors";

	/**
	 * Kudos_Donor constructor.
	 *
	 * @since   1.1.0
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * @param string $email
	 * @param string $customer_id
	 * @param string $payment_frequency
	 * @param string $name
	 * @param string|null $street
	 * @param string|null $postcode
	 * @param string|null $city
	 * @param string|null $country
	 *
	 * @return bool|false|int
	 * @since   1.1.0
	 */
	public function create_donor($email, $customer_id, $payment_frequency, $name=null, $street=null, $postcode=null, $city=null,  $country=null) {
		return $this->wpdb->insert(
			$this->wpdb->prefix . self::TABLE,
			[
				'email' => $email,
				'name' => $name,
				'street' => $street,
				'postcode' => $postcode,
				'city' => $city,
				'country' => $country,
				'customer_id' => $customer_id,
				'payment_frequency' => $payment_frequency
			]
		);
	}

	/**
	 * @param string $email
	 * @param string $payment_frequency
	 * @param string $name
	 * @param string|null $street
	 * @param string|null $postcode
	 * @param string|null $city
	 * @param string|null $country
	 *
	 * @return bool|false|int
	 * @since   1.1.0
	 */
	public function update_donor($email, $payment_frequency, $name=null, $street=null, $postcode=null, $city=null,  $country=null) {
		return $this->wpdb->update(
			$this->wpdb->prefix . self::TABLE,
			[
				'name' => $name,
				'street' => $street,
				'postcode' => $postcode,
				'city' => $city,
				'country' => $country,
				'payment_frequency' => $payment_frequency
			], [
				'email' => $email,
			]
		);
	}

	/**
	 * @param string $email // Email address used to find a donor
	 * @param array $fields // Specify fields to fetch from database
	 *
	 * @return array|object|void|null
	 * @since   1.1.0
	 */
	public function get_donor($email, array $fields=['*']) {

		$wpdb = $this->wpdb;
		$table = $this->wpdb->prefix . self::TABLE;
		$columns = implode(', ', $fields);

		return $wpdb->get_row( $wpdb->prepare( "
			SELECT $columns FROM $table WHERE email = '%s'
		", $email ) );
	}
}