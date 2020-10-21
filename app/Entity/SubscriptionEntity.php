<?php

namespace Kudos\Entity;

use DateTime;
use Kudos\Service\MapperService;

class SubscriptionEntity extends AbstractEntity {

	/**
	 * Table name without prefix
	 * @var string
	 */
	protected const TABLE = "kudos_subscriptions";
	/**
	 * @var int
	 */
	public $value;
	/**
	 * @var string
	 */
	public $currency;
	/**
	 * @var string
	 */
	public $frequency;
	/**
	 * @var int
	 */
	public $years;
	/**
	 * @var string
	 */
	public $status;
	/**
	 * @var string
	 */
	public $customer_id;
	/**
	 * @var string
	 */
	public $transaction_id;
	/**
	 * @var string
	 */
	public $subscription_id;
	/**
	 * @var DateTime
	 */
	public $last_updated;

	/**
	 * Subscription constructor
	 *
	 * @param null|array $atts
	 */
	public function __construct( $atts = null ) {

		parent::__construct( $atts );

	}

	/**
	 * Gets donor associated with subscription
	 *
	 * @return EntityInterface|null
	 * @since   2.0.0
	 */
	public function get_donor() {

		$mapper = new MapperService( DonorEntity::class );

		return $mapper->get_one_by( [ 'customer_id' => $this->customer_id ] );

	}
}