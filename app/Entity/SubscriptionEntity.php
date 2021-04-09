<?php

namespace Kudos\Entity;

use Kudos\Service\MapperService;

class SubscriptionEntity extends AbstractEntity {

	/**
	 * Table name without prefix
	 *
	 * @var string
	 */
	protected const TABLE = 'kudos_subscriptions';
	/**
	 * Donation value
	 *
	 * @var int
	 */
	public $value;
	/**
	 * Currency used for donation (EUR)
	 *
	 * @var string
	 */
	public $currency;
	/**
	 * Frequency of payment
	 *
	 * @var string
	 */
	public $frequency;
	/**
	 * Number of years subscription lasts
	 *
	 * @var int
	 */
	public $years;
	/**
	 * Current status of subscription
	 *
	 * @var string
	 */
	public $status;
	/**
	 * Mollie customer id
	 *
	 * @var string
	 */
	public $customer_id;
	/**
	 * Id of transaction used to make subscription
	 *
	 * @var string
	 */
	public $transaction_id;
	/**
	 * Mollie subscription id
	 *
	 * @var string
	 */
	public $subscription_id;

	/**
	 * Gets donor associated with subscription
	 *
	 * @return DonorEntity|null
	 * @since   2.0.0
	 */
	public function get_donor(): ?EntityInterface {

		$mapper = new MapperService( DonorEntity::class );
		/** @var DonorEntity $donor */
		$donor = $mapper->get_one_by( [ 'customer_id' => $this->customer_id ] );

		return $donor ?? null;

	}
}
