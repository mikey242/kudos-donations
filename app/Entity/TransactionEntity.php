<?php

namespace Kudos\Entity;

use Kudos\Service\MapperService;

class TransactionEntity extends AbstractEntity {

	/**
	 * Table name without prefix
	 *
	 * @var string
	 */
	protected const TABLE = 'kudos_transactions';
	/**
	 * Value of donation
	 *
	 * @var int
	 */
	public $value;
	/**
	 * Currency of donation (EUR)
	 *
	 * @var string
	 */
	public $currency;
	/**
	 * Status of transaction
	 *
	 * @var string
	 */
	public $status;
	/**
	 * Payment method
	 *
	 * @var string
	 */
	public $method;
	/**
	 * Mode used ('test' or 'live')
	 *
	 * @var string
	 */
	public $mode;
	/**
	 * Sequence type (oneoff, first, recurring)
	 *
	 * @var string
	 */
	public $sequence_type;
	/**
	 * Mollie transaction id
	 *
	 * @var string
	 */
	public $transaction_id;
	/**
	 * Kudos order id
	 *
	 * @var string
	 */
	public $order_id;
	/**
	 * Mollie customer id
	 *
	 * @var string
	 */
	public $customer_id;
	/**
	 * Mollie subscription id
	 *
	 * @var string
	 */
	public $subscription_id;
	/**
	 * Campaign label for donation
	 *
	 * @var string
	 */
	public $campaign_label;
	/**
	 * Refunds serialized array
	 *
	 * @var string
	 */
	public $refunds;

	/**
	 * Gets donor associated with transaction
	 *
	 * @return EntityInterface|DonorEntity null
	 * @since   2.0.0
	 */
	public function get_donor() {

		$mapper = new MapperService( DonorEntity::class );
		$donor  = $mapper->get_one_by( [ 'customer_id' => $this->customer_id ] );

		return $donor ?? null;

	}

	/**
	 * Returns unserialized array of refund data
	 *
	 * @return object|false
	 * @since   2.0.0
	 */
	public function get_refund() {

		$refunds = $this->refunds;

		if ( $refunds ) {
			$result = json_decode( $refunds );
			if ( json_last_error() == JSON_ERROR_NONE ) {
				return $result;
			}
		}

		return false;

	}
}