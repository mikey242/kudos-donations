<?php

namespace Kudos\Service;

use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\BaseCollection;
use Mollie\Api\Resources\Payment;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

class MollieService extends AbstractService {

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
	 * Our REST API webhook
	 *
	 * @var string
	 */
	private $webhook_url;


	/**
	 * Mollie constructor.
	 *
	 * @since      1.0.0
	 */
	public function __construct() {

		$this->mollie_api  = new MollieApiClient();
		$this->api_mode    = Settings::get_setting( 'mollie_api_mode' );
		$this->api_key     = Settings::get_setting( 'mollie_' . $this->api_mode . '_api_key' );
		$this->webhook_url = $_ENV['WEBHOOK_URL'] ?? rest_url( 'kudos/v1/mollie/payment/webhook' );

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
	 * Creates a payment and returns it as an object
	 *
	 * @param string      $value Value of payment.
	 * @param string      $interval Interval of payment (oneoff, first, recurring).
	 * @param string      $years Number of years for subscription.
	 * @param string      $redirect_url URL to redirect customer to on payment completion.
	 * @param string|null $campaign_label Campaign name to associate payment to.
	 * @param string|null $name Name of donor.
	 * @param string|null $email Email of donor.
	 * @param string|null $customer_id Mollie customer id.
	 *
	 * @return bool|object
	 * @since      1.0.0
	 */
	public function create_payment(
		string $value,
		string $interval,
		string $years,
		string $redirect_url,
		string $campaign_label = null,
		string $name = null,
		string $email = null,
		string $customer_id = null
	) {

		$mollie_api = $this->mollie_api;
		$order_id   = Utils::generate_id( 'kdo_' );
		$currency   = 'EUR';
		$value      = number_format( $value, 2 );

		// Set payment frequency.
		$frequency_text = Utils::get_frequency_name( $interval );
		$sequence_type  = ( 'oneoff' === $interval ? 'oneoff' : 'first' );

		// Create payment settings.
		$payment_array = [
			"amount"       => [
				'currency' => $currency,
				'value'    => $value,
			],
			'redirectUrl'  => $redirect_url,
			'webhookUrl'   => $this->webhook_url,
			'sequenceType' => $sequence_type,
			'description'  => sprintf(
				/* translators: %s: The order id */
				__( 'Kudos Donation (%1$s) - %2$s', 'kudos-donations' ),
				$frequency_text,
				$order_id
			),
			'metadata'     => [
				'order_id'       => $order_id,
				'interval'       => $interval,
				'years'          => $years,
				'email'          => $email,
				'name'           => $name,
				'campaign_label' => $campaign_label,
			],
		];

		// Link payment to customer if specified.
		if ( $customer_id ) {
			$payment_array['customerId'] = $customer_id;
		}

		try {
			$payment = $mollie_api->payments->create( $payment_array );

			$transaction = new TransactionEntity(
				[
					'order_id'       => $order_id,
					'customer_id'    => $customer_id,
					'value'          => $value,
					'currency'       => $currency,
					'status'         => $payment->status,
					'mode'           => $payment->mode,
					'sequence_type'  => $payment->sequenceType,
					'campaign_label' => $campaign_label,
				]
			);

			$mapper = new MapperService( TransactionEntity::class );
			$mapper->save( $transaction );

			// Add order id query arg to return url if option to show message enabled.
			if ( get_option( '_kudos_return_message_enable' ) ) {
				$redirect_url         = add_query_arg(
					[
						'kudos_action'   => 'order_complete',
						'kudos_order_id' => $order_id,
						'kudos_token'    => $transaction->create_secret(),
					],
					$redirect_url
				);
				$payment->redirectUrl = $redirect_url;
				$payment->update();
				$mapper->save( $transaction );
			}

			$this->logger->info(
				'New payment created',
				[ 'oder_id' => $order_id, 'sequence_type' => $payment->sequenceType ]
			);

			return $payment;

		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage(), [ 'payment' => $payment_array ] );

			return false;
		}

	}

	/**
	 * Returns all subscriptions for customer
	 *
	 * @param string $customer_id Mollie customer id.
	 *
	 * @return BaseCollection|bool
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
	 * Create a Mollie customer.
	 *
	 * @param string $email Donor email address.
	 * @param string $name Donor name.
	 *
	 * @return bool|object
	 * @since   2.0.0
	 */
	public function create_customer( string $email, string $name ) {

		$mollie_api = $this->mollie_api;

		$customer_array = [
			'email' => $email,
		];

		if ( $name ) {
			$customer_array['name'] = $name;
		}

		try {
			return $mollie_api->customers->create( $customer_array );
		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage() );

			return false;
		}

	}

	/**
	 * Cancel the specified subscription
	 *
	 * @param string      $subscription_id Mollie subscription id.
	 * @param null|string $customer_id Mollie customer id.
	 *
	 * @return bool
	 * @since   2.0.0
	 */
	public function cancel_subscription( string $subscription_id, $customer_id = null ) {

		$mollie_api = $this->mollie_api;
		$mapper     = new MapperService( SubscriptionEntity::class );

		/** @var SubscriptionEntity $subscription */
		$subscription = $mapper->get_one_by( [ 'subscription_id' => $subscription_id ] );

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

		try {
			$customer            = $mollie_api->customers->get( $customer_id );
			$mollie_subscription = $customer->cancelSubscription( $subscription_id );

			if ( $mollie_subscription ) {

				$this->logger->info(
					'Subscription cancelled.',
					[ 'customer_id' => $customer_id, 'subscription_id' => $subscription_id ]
				);

				if ( null !== $subscription ) {
					$subscription->set_fields(
						[
							'status' => 'cancelled',
						]
					);

					$mapper->save( $subscription );
				}

				return true;
			}

			return false;

		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage(), [ $customer_id, $subscription_id ] );

			return false;
		}

	}

	/**
	 * Check the Mollie Api key associated with the Api mode
	 *
	 * @param WP_REST_Request $request Request array.
	 *
	 * @since    1.1.0
	 */
	public function check_api_keys( WP_REST_Request $request ) {

		Settings::update_setting( 'mollie_connected', 0 );

		$mode    = sanitize_text_field( $request['apiMode'] );
		$api_key = sanitize_text_field( $request[ $mode . 'Key' ] );

		// Check that the api key corresponds to the mode.
		if ( substr( $api_key, 0, 4 ) !== $mode ) {
			wp_send_json_error(
				/* translators: %s: API mode */
				sprintf( __( '%1$s API key should begin with %2$s', 'kudos-donations' ), ucfirst( $mode ), $mode . '_' )
			);
		}

		// Test the api key.
		$result = $this->test_api_connection( $api_key );

		if ( $result ) {
			Settings::update_setting( 'mollie_' . $mode . '_api_key', $api_key );
			Settings::update_setting( 'mollie_api_mode', $mode );
			Settings::update_setting( 'mollie_connected', 1 );
			wp_send_json_success(
			/* translators: %s: API mode */
				sprintf( __( '%s API key connection was successful!', 'kudos-donations' ), ucfirst( $mode ) )
			);
		} else {
			wp_send_json_error(
			/* translators: %s: API mode */
				sprintf( __( 'Error connecting with Mollie, please check the %s API key and try again.', 'kudos-donations' ), ucfirst( $mode ) )
			);
		}
	}

	/**
	 * Checks the provided api key by attempting to get associated payments
	 *
	 * @param string $api_key API key to test.
	 *
	 * @return bool
	 * @since      1.0.0
	 */
	public function test_api_connection( string $api_key ) {

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
	 * Mollie webhook action
	 *
	 * @param WP_REST_Request $request Request array.
	 *
	 * @return mixed|WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since    1.0.0
	 */
	public function rest_api_mollie_webhook( WP_REST_Request $request ) {

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
			'Webhook requested by Mollie.',
			[
				'transaction_id' => $id,
				'status'         => $payment->status,
				'sequence_type'  => $payment->sequenceType
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
		do_action( 'kudos_mollie_webhook', $payment );

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
			$subscription_id = $payment->subscriptionId;
			$customer_id     = $payment->customerId;

			try {
				$customer          = $this->mollie_api->customers->get( $customer_id );
				$subscription_meta = $customer->getSubscription( $subscription_id )->metadata;
				if ( array_key_exists( 'campaign_label', $subscription_meta ) ) {
					$campaign_label = $subscription_meta->campaign_label;
					$transaction->set_fields(
						[
							'campaign_label' => $campaign_label,
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
			} catch ( ApiException $e ) {
				$this->logger->warning( $e->getMessage() );
			}
		}

		// Save transaction to database.
		$mapper->save( $transaction );

		if ( $payment->isPaid() && ! $payment->hasRefunds() && ! $payment->hasChargebacks() ) {

			// Get schedule processing for later.
			if ( class_exists( 'ActionScheduler' ) ) {
				if ( false === as_next_scheduled_action( 'kudos_process_paid_transaction', [ $order_id ] ) ) {
					$timestamp = strtotime( '+1 minute' );
					as_schedule_single_action( $timestamp, 'kudos_process_paid_transaction', [ $order_id ] );
					$this->logger->debug(
						'Action "kudos_process_paid_transaction" scheduled',
						[
							'datetime' => wp_date( 'Y-m-d H:i:s', $timestamp ),
						]
					);
				}
			} else {
				do_action( 'kudos_process_paid_transaction', $order_id );
			}

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
	 * Create a subscription
	 *
	 * @param TransactionEntity $transaction Transaction object.
	 * @param string            $mandate_id Mollie mandate id.
	 * @param string            $interval Subscription interval.
	 * @param string            $years Number of years for subscription.
	 *
	 * @return bool|object
	 * @since      2.0.0
	 */
	public function create_subscription(
		TransactionEntity $transaction,
		string $mandate_id,
		string $interval,
		string $years
	) {

		$mollie_api  = $this->mollie_api;
		$customer_id = $transaction->customer_id;
		$start_date  = gmdate( 'Y-m-d', strtotime( '+' . $interval ) );
		$currency    = 'EUR';
		$value       = number_format( $transaction->value, 2 );

		$subscription_array = [
			'amount'      => [
				'value'    => $value,
				'currency' => $currency,
			],
			'webhookUrl'  => $this->webhook_url,
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
				'campaign_label' => $transaction->campaign_label,
			],
		];

		if ( 'test' === $transaction->mode ) {
			unset( $subscription_array['startDate'] );  // Disable for test mode.
		}

		if ( $years && $years > 0 ) {
			$subscription_array['times'] = Utils::get_times_from_years( $years, $interval );
		}

		try {
			$customer = $mollie_api->customers->get( $customer_id );
			$mandate  = $mollie_api->mandates->getFor( $customer, $mandate_id );

			if ( 'pending' === ! $mandate->status || 'valid' === ! $mandate->status ) {
				$this->logger->error(
					'Cannot create subscription as customer has no valid mandates.',
					[ $customer_id ]
				);

				return false;
			}

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

		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage(), [ $customer_id, $subscription_array ] );

			return false;
		}

	}
}
