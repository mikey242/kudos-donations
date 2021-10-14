<?php

namespace Kudos\Entity;

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
	 * ID of transaction used to make subscription
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
}
