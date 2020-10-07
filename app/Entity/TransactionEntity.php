<?php

namespace Kudos\Entity;

use DateTime;
use Kudos\Service\MapperService;

class TransactionEntity extends AbstractEntity {

	/**
	 * Table name without prefix
	 * @var string
	 */
	public const TABLE = "kudos_transactions";
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
	public $status;
	/**
	 * @var string
	 */
	public $method;
	/**
	 * @var string
	 */
	public $mode;
	/**
	 * @var string
	 */
	public $sequence_type;
	/**
	 * @var string
	 */
	public $transaction_id;
	/**
	 * @var string
	 */
	public $order_id;
	/**
	 * @var string
	 */
	public $customer_id;
	/**
	 * @var string
	 */
	public $subscription_id;
	/**
	 * @var string
	 */
	public $campaign_label;
	/**
	 * @var string
	 */
	public $refunds;
	/**
	 * @var DateTime
	 */
	public $last_updated;

	/**
	 * Transaction constructor
	 *
	 * @param null|array $atts
	 *
	 * @since   2.0.0
	 */
	public function __construct( $atts = null ) {

		parent::__construct( $atts );

	}

	/**
	 * Gets donor associated with transaction
	 *
	 * @return DonorEntity|AbstractEntity|null
	 * @since   2.0.0
	 */
	public function get_donor() {

		$mapper = new MapperService( DonorEntity::class );

		return $mapper->get_one_by( [ 'customer_id' => $this->customer_id ] );

	}

	/**
	 * Returns unserialized array of refund data
	 *
	 * @return array|false
	 * @since   2.0.0
	 */
	public function get_refund() {

		$refunds = $this->refunds;
		if ( is_serialized( $refunds ) ) {
			return unserialize( $refunds );
		}

		return false;

	}
}