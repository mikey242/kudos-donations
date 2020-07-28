<?php

namespace Kudos\Entity;

use Kudos\Entity;

class Donor extends Entity {

	public const TABLE = "kudos_donors";

	/**
	 * Add donor_created
	 *
	 * @param $atts
	 * @since   2.0.0
	 */
	public function __construct($atts=null) {
		parent::__construct($atts);
		$this->fields['donor_created'] = current_time('mysql');
	}

	/**
	 * Gets all transactions for current user
	 *
	 * @return array|object
	 */
	public function get_transactions() {
		$transaction = new Transaction();
		return $transaction->get_all(['customer_id' => $this->fields['customer_id']]);
	}

}