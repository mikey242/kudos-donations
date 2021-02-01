<?php

namespace Kudos\Service\Vendor;

use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\MapperService;
use Kudos\Service\PaymentService;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Customer;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Subscription;
use Mollie\Api\Resources\SubscriptionCollection;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class MollieVendor extends AbstractVendor {

	/**
	 * This is the name of the vendor as it will appear in the logs
	 */
	const VENDOR_NAME = 'Mollie';

	/**
	 * Instance of MollieApiClient
	 *
	 * @var MollieApiClient
	 */
	private $mollie_api;
	/**
	 * The API mode (test or live)
	 *
	 * @var mixed
	 */
	private $api_mode;
	/**
	 * The API key to use
	 *
	 * @var mixed
	 */
	private $api_key;

	/**
	 * Mollie constructor.
	 *
	 * @since      1.0.0
	 */
	public function __construct() {

		$this->mollie_api = new MollieApiClient();
		$this->api_mode   = Settings::get_setting( 'mollie_api_mode' );
		$this->api_key    = Settings::get_setting( 'mollie_' . $this->api_mode . '_api_key' );

		if ( $this->api_key ) {
			try {
				$this->mollie_api->setApiKey( $this->api_key );
			} catch ( ApiException $e ) {
				$this->logger->critical( $e->getMessage() );
			}
		}

		parent::__construct();

	}

	/**
	 * Returns all subscriptions for customer
	 *
	 * @param string $customer_id Mollie customer id.
	 *
	 * @return SubscriptionCollection|false
	 * @since   2.0.0
	 */
	public function get_subscriptions( string $customer_id ) {

		$mollie_api = $this->mollie_api;

		try {
			$customer = $mollie_api->customers->get( $customer_id );

			return $customer->subscriptions();
		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage() );

			return false;
		}

	}

	/**
	 * Cancel the specified subscription
	 *
	 * @param string $subscription_id Mollie subscription id.
	 * @param null|string $customer_id Mollie customer id.
	 *
	 * @return bool
	 * @since   2.0.0
	 */
	public function cancel_subscription( string $subscription_id, $customer_id = null ): bool {

		// Get customer id from subscription if not provided
		if ( ! $customer_id ) {

			if ( empty( $subscription ) ) {
				return false;
			}

			if ( 'active' !== $subscription->status ) {
				$this->logger->debug( 'Subscription already cancelled.', [ 'subscription_id' => $subscription_id ] );

				return false;
			}

			$customer_id = $subscription->customer_id;
		}

		$customer = $this->get_customer( $customer_id );

		return $customer->cancelSubscription( $subscription_id );
	}

	/**
	 * Checks the provided api key by attempting to get associated payments
	 *
	 * @param string $api_key API key to test.
	 *
	 * @return bool
	 * @since      1.0.0
	 */
	public function test_api_connection( string $api_key ): bool {

		if ( ! $api_key ) {
			return false;
		}

		try {
			// Perform test call to verify api key.
			$mollie_api = $this->mollie_api;
			$mollie_api->setApiKey( $api_key );
			$mollie_api->payments->page();
		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage() );

			return false;
		}

		return true;

	}

	/**
	 * Gets specified payment
	 *
	 * @param string $mollie_payment_id Mollie payment id.
	 *
	 * @return bool|Payment
	 * @since      1.0.0
	 */
	public function get_payment( string $mollie_payment_id ) {

		$mollie_api = $this->mollie_api;

		try {
			return $mollie_api->payments->get( $mollie_payment_id );
		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage() );
		}

		return false;

	}

	/**
	 * Create a Mollie customer.
	 *
	 * @param string $email Donor email address.
	 * @param string $name Donor name.
	 *
	 * @return bool|Customer
	 * @since   2.0.0
	 */
	public function create_customer( string $email, string $name ) {

		$customer_array = [
			'email' => $email,
		];

		if ( $name ) {
			$customer_array['name'] = $name;
		}

		try {
			return $this->mollie_api->customers->create( $customer_array );
		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage() );

			return false;
		}
	}

	/**
	 * Get the customer from Mollie
	 *
	 * @param $customer_id
	 *
	 * @return Customer|null
	 */
	public function get_customer( $customer_id ): ?Customer {

		try {
			return $this->mollie_api->customers->get( $customer_id );
		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage() );

			return null;
		}

	}

	/**
	 * Creates a payment and returns it as an object
	 *
	 * @param array $payment_array Parameters to pass to mollie to create a payment.
	 *
	 * @return bool|Payment
	 * @since      1.0.0
	 */
	public function create_payment( array $payment_array ): ?Payment {

		try {

			return $this->mollie_api->payments->create( $payment_array );

		} catch ( ApiException $e ) {

			$this->logger->critical( $e->getMessage(), [ 'payment' => $payment_array ] );

			return false;

		}

	}

	/**
	 * Creates a subscription based on the provided TransactionEntity
	 *
	 * @param TransactionEntity $transaction
	 * @param string $mandate_id
	 * @param string $interval
	 * @param string $years
	 *
	 * @return false|Subscription
	 */
	public function create_subscription(
		TransactionEntity $transaction,
		string $mandate_id,
		string $interval,
		string $years
	) {

		$customer_id = $transaction->customer_id;
		$start_date  = gmdate( 'Y-m-d', strtotime( '+' . $interval ) );
		$currency    = 'EUR';
		$value       = number_format( $transaction->value, 2 );

		$subscription_array = [
			'amount'      => [
				'value'    => $value,
				'currency' => $currency,
			],
			'webhookUrl'  => $_ENV['WEBHOOK_URL'] ?? rest_url( PaymentService::REST_NAMESPACE . PaymentService::WEBHOOK_ROUTE ),
			'mandateId'   => $mandate_id,
			'interval'    => $interval,
			'startDate'   => $start_date,
			'description' => sprintf(
			/* translators: %1$s: Subscription interval. %2$s: Order id. */
				__( 'Kudos Subscription (%1$s) - %2$s', 'kudos-donations' ),
				$interval,
				$transaction->order_id
			),
			'metadata'    => [
				'campaign_id' => $transaction->campaign_id,
			],
		];

		if ( 'test' === $transaction->mode ) {
			unset( $subscription_array['startDate'] );  // Disable for test mode.
		}

		if ( $years && $years > 0 ) {
			$subscription_array['times'] = Utils::get_times_from_years( $years, $interval );
		}

		$customer      = $this->get_customer( $customer_id );
		$valid_mandate = $this->check_mandate( $customer, $mandate_id );

		// Create subscription if valid mandate found
		if ( $valid_mandate ) {
			$subscription = $customer->createSubscription( $subscription_array );
			if ( $subscription ) {
				$mapper             = new MapperService( SubscriptionEntity::class );
				$kudos_subscription = new SubscriptionEntity(
					[
						'transaction_id'  => $transaction->transaction_id,
						'customer_id'     => $customer_id,
						'frequency'       => $interval,
						'years'           => $years,
						'value'           => $value,
						'currency'        => $currency,
						'subscription_id' => $subscription->id,
						'status'          => $subscription->status,
					]
				);
				$mapper->save( $kudos_subscription );

				return $subscription;
			}

			$this->logger->error( 'Failed to create subscription', [ $transaction ] );

			return false;
		}

		// No valid mandates
		$this->logger->error(
			'Cannot create subscription as customer has no valid mandates.',
			[ $customer_id ]
		);

		return false;
	}

	/**
	 * Check the provided customer for valid mandates
	 *
	 * @param Customer $customer
	 * @param string $mandate_id
	 *
	 * @return bool
	 */
	private function check_mandate( Customer $customer, string $mandate_id ): bool {

		$mandate = $customer->getMandate( $mandate_id );

		if ( $mandate->isValid() || $mandate->isPending() ) {
			return true;
		}

		return false;
	}

	/**
	 * Mollie webhook action
	 *
	 * @param WP_REST_Request $request Request array.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @since    1.0.0
	 */
	public function rest_webhook( WP_REST_Request $request ) {

		// ID is case sensitive (e.g: tr_HUW39xpdFN).
		$id = sanitize_text_field( $request->get_param( 'id' ) );

		/**
		 * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
		 */
		$response = rest_ensure_response(
			[
				'success' => true,
				'id'      => $id,
			]
		);

		$response->add_link( 'self', rest_url( $request->get_route() ) );

		/**
		 * Get the payment object from Mollie
		 *
		 * @var Payment $payment Mollie payment object.
		 */
		$payment = $this->get_payment( $id );
		$this->logger->info(
			"Webhook requested by $this.",
			[
				'transaction_id' => $id,
				'status'         => $payment->status,
				'sequence_type'  => $payment->sequenceType,
			]
		);

		/**
		 *
		 * To not leak any information to malicious third parties, it is recommended
		 * to return a 200 OK response even if the ID is not known to your system.
		 *
		 * @link https://docs.mollie.com/guides/webhooks#how-to-handle-unknown-ids
		 */
		if ( null === $payment ) {
			return $response;
		}

		//Create webhook action.
		do_action( 'kudos_mollie_webhook_requested', $payment );

		//Get required data from payment object.
		$transaction_id = $payment->id;
		$order_id       = $payment->metadata->order_id ?? Utils::generate_id( 'kdo_' );
		$amount         = $payment->amount;

		// Get transaction from database.
		$mapper      = new MapperService( TransactionEntity::class );
		$transaction = $mapper->get_one_by(
			[
				'order_id'       => $order_id,
				'transaction_id' => $transaction_id,
			],
			'OR'
		);

		// Create new transaction if none found.
		if ( null === $transaction ) {
			$transaction = new TransactionEntity(
				[
					'order_id' => $order_id,
				]
			);
		}

		// Add refund if present.
		if ( $payment->hasRefunds() ) {

			do_action( 'kudos_mollie_refund', $order_id );

			$transaction->set_fields(
				[
					'refunds' => json_encode(
						[
							'refunded'  => $payment->getAmountRefunded(),
							'remaining' => $payment->getAmountRemaining(),
						]
					),
				]
			);

			$this->logger->info( 'Payment refunded', [ $transaction ] );
			do_action( 'kudos_process_refund', $order_id );

		} else {
			// Check if status is the same (in case of multiple webhook calls).
			if ( $transaction->status === $payment->status ) {
				$this->logger->debug( 'Duplicate webhook detected. Ignoring.', [ $transaction ] );

				return $response;
			}
		}

		// Update payment.
		$transaction->set_fields(
			[
				'status'          => $payment->status,
				'transaction_id'  => $transaction_id,
				'customer_id'     => $payment->customerId,
				'value'           => $amount->value,
				'currency'        => $amount->currency,
				'sequence_type'   => $payment->sequenceType,
				'method'          => $payment->method,
				'mode'            => $payment->mode,
				'subscription_id' => $payment->subscriptionId,
			]
		);

		// Add campaign label to recurring payments.
		if ( $payment->hasSequenceTypeRecurring() ) {
			$subscription_id   = $payment->subscriptionId;
			$customer_id       = $payment->customerId;
			$customer          = $this->get_customer( $customer_id );
			$subscription_meta = $customer->getSubscription( $subscription_id )->metadata;
			if ( array_key_exists( 'campaign_id', $subscription_meta ) ) {
				$campaign_id = $subscription_meta->campaign_id;
				$transaction->set_fields(
					[
						'campaign_id' => $campaign_id,
					]
				);
			} else {
				$this->logger->info(
					'No campaign label found for recurring payment',
					[
						'customer_id'     => $customer_id,
						'subscription_id' => $subscription_id,
					]
				);
			}
		}

		// Save transaction to database.
		$mapper->save( $transaction );

		if ( $payment->isPaid() && ! $payment->hasRefunds() && ! $payment->hasChargebacks() ) {

			// Get schedule processing for later.
			Utils::schedule_action( strtotime( '+1 minute' ), 'kudos_mollie_paid_email', [ $order_id ] );

			// Set up recurring payment if sequence is first.
			if ( $payment->hasSequenceTypeFirst() ) {
				$this->logger->debug( 'Creating subscription', [ $transaction ] );
				$this->create_subscription(
					$transaction,
					$payment->mandateId,
					$payment->metadata->interval,
					$payment->metadata->years
				);
			}
		}

		return $response;
	}

	/**
	 * Returns the api mode
	 *
	 * @return string
	 */
	public function get_api_mode(): string {

		return $this->api_mode;

	}
}
