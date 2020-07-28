<?php

namespace Kudos;

use Kudos\Entity\Mapper;
use Kudos\Entity\Subscription;
use Kudos\Entity\Transaction;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\BaseCollection;
use Mollie\Api\Resources\Customer;
use Mollie\Api\Resources\Payment;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Kudos_Mollie
{
	/**
	 * @var Kudos_Logger
	 */
	private $logger;
	/**
	 * @var MollieApiClient
	 */
	private $mollieApi;
	/**
	 * @var mixed
	 */
	private $apiMode;
	/**
	 * @var mixed
	 */
	private $apiKey;


	/**
	 * Mollie constructor.
	 *
	 * @since      1.0.0
	 */
	public function __construct() {
		$this->logger = new Kudos_Logger();
		$this->mollieApi = new MollieApiClient();
		$this->apiMode = get_option('_kudos_mollie_api_mode');
		$this->apiKey = get_option('_kudos_mollie_'.$this->apiMode.'_api_key');
		if($this->apiKey) {
			try {
				$this->mollieApi->setApiKey($this->apiKey);
			} catch (ApiException $e) {
				$this->logger->critical($e->getMessage());
			}
		}
	}

	/**
	 * Checks the provided api key by attempting to get associated payments
	 *
	 * @param $apiKey
	 *
	 * @return bool
	 * @since      1.0.0
	 */
	public function check_api_key($apiKey) {

		if(!$apiKey) {
			return false;
		}

		try {
			// Perform test call to verify api key
			$mollieApi = $this->mollieApi;
			$mollieApi->setApiKey($apiKey);
			$mollieApi->payments->page();
		} catch ( ApiException $e) {
			$this->logger->critical($e->getMessage());
			return false;
		}
		return true;
	}

	/**
	 * Gets specified payment
	 *
	 * @param $mollie_payment_id
	 *
	 * @return bool|Payment
	 * @since      1.0.0
	 */
	public function get_payment($mollie_payment_id) {
		$mollieApi = $this->mollieApi;
		try {
			return $mollieApi->payments->get($mollie_payment_id);
		} catch (ApiException $e) {
			$this->logger->critical($e->getMessage());
		}
		return false;
	}

	/**
	 * Creates a payment and returns it as an object
	 *
	 * @param $value
	 * @param string $interval
	 * @param string $years
	 * @param string $redirectUrl
	 * @param string $donation_label
	 * @param string|null $name
	 * @param string|null $email
	 * @param string|null $customerId
	 *
	 * @return bool|object
	 * @since      1.0.0
	 */
	public function create_payment($value, $interval, $years, $redirectUrl, $donation_label, $name=null, $email=null, $customerId=null) {

		$mollieApi = $this->mollieApi;
		$order_id = generate_id('kdo_');
		$currency = 'EUR';
		$value = number_format($value, 2);

		// Add order id query arg to return url if option to show message enabled
		if(get_option('_kudos_return_message_enable')) {
			$redirectUrl = add_query_arg('kudos_order_id', base64_encode($order_id), $redirectUrl);
			$redirectUrl = add_query_arg('kudos_token', wp_create_nonce('kudos_check_order-' . $order_id), $redirectUrl);
		}

		// Set payment frequency
		$frequency_text = get_frequency_name($interval);
		$sequenceType = ($interval === 'oneoff' ? 'oneoff' : 'first');

		// Create payment settings
		$paymentArray = [
			"amount" => [
				"currency" => $currency,
				"value" => $value
			],
			"redirectUrl" => $redirectUrl,
			"webhookUrl" => rest_url('kudos/v1/mollie/payment/webhook'),
            "sequenceType" => $sequenceType,
			/* translators: %s: The order id */
			"description" => sprintf(__("Kudos Donation (%s) - %s", 'kudos-donations'), $frequency_text, $order_id),
			'metadata' => [
				'order_id' => $order_id,
				'interval' => $interval,
				'years' => $years,
				'email' => $email,
				'name' => $name
			]
		];

		if(WP_DEBUG) {
			$paymentArray['webhookUrl'] = 'https://ea4c3bc4351d.eu.ngrok.io/wp-json/kudos/v1/mollie/payment/webhook';
		}

		// Link payment to customer if specified
		if($customerId) {
			$paymentArray['customerId'] = $customerId;
		}

		try {
			$payment = $mollieApi->payments->create($paymentArray);

			$transaction = new Transaction([
				'order_id' => $order_id,
				'customer_id' => $customerId,
				'value' => $value,
				'currency' => $currency,
				'status' => $payment->status,
				'mode' => $payment->mode,
				'sequence_type' => $payment->sequenceType,
				'donation_label' => $donation_label,
			]);

			$transaction->save();

			$this->logger->info('New payment created', ['oder_id' => $order_id, 'sequence_type' => $payment->sequenceType]);
			return $payment;

		} catch (ApiException $e) {
			$this->logger->critical($e->getMessage(), ['payment' => $paymentArray]);
			return false;
		}

	}

	/**
	 * Returns all subscriptions for customer
	 *
	 * @since   2.0.0
	 * @param $customerId
	 *
	 * @return BaseCollection|bool
	 */
	public function get_subscriptions($customerId) {

		$mollieApi = $this->mollieApi;

		try {
			/** @var Customer $customer */
			$customer = $mollieApi->customers->get($customerId);
			return $customer->subscriptions();
		} catch (ApiException $e) {
			$this->logger->critical($e->getMessage());
			return false;
		}

	}

	/**
	 * Create a subscription
	 *
	 * @param Transaction $transaction
	 * @param $mandateId
	 * @param $interval
	 * @param $years
	 *
	 * @return bool|object
	 * @since      2.0.0
	 */
	public function create_subscription($transaction, $mandateId, $interval, $years) {

        $mollieApi = $this->mollieApi;
        $customer_id = $transaction->fields['customer_id'];
        $startDate = date("Y-m-d", strtotime("+" . $interval));
        $currency = 'EUR';
        $value = number_format($transaction->fields['value'], 2);

        $subscriptionArray = [
            "amount" => [
                "value" => $value,
                "currency" => $currency
            ],
	        "mandateId" => $mandateId,
            "interval" => $interval,
            "startDate" => $startDate,  // Disable for test mode
            "description" => sprintf(__('Kudos Subscription (%s)', 'kudos-donations'), $interval),
            "webhookUrl" => rest_url('kudos/v1/mollie/subscription/webhook'),
        ];

        if(WP_DEBUG) {
	        $subscriptionArray['webhookUrl'] = 'https://ea4c3bc4351d.eu.ngrok.io/wp-json/kudos/v1/mollie/payment/webhook';
	        unset($subscriptionArray['startDate']);  // Disable for test mode
        }

        if($years && $years > 0) {
            $subscriptionArray["times"] = get_times_from_years($years, $interval);
        }

        try {
            /** @var Customer $customer */
            $customer = $mollieApi->customers->get($customer_id);
            $mandate = $mollieApi->mandates->getFor($customer, $mandateId);

			if(!$mandate->status === 'pending' || !$mandate->status === 'valid') {
				$this->logger->error('Cannot create subscription as customer has no valid mandates.', [$customer_id]);
				return false;
			}

	        $subscription = $customer->createSubscription($subscriptionArray);

	        if($subscription) {
		        $kudos_subscription = new Subscription([
			        'transaction_id' => $transaction->fields['transaction_id'],
			        'customer_id' => $customer_id,
			        'frequency' => $interval,
			        'years' => $years,
			        'value' => $value,
			        'currency' => $currency,
			        'subscription_id' => $subscription->id,
			        'status' => $subscription->status
		        ]);
				$kudos_subscription->save();
		        return $subscription;
	        }

	        return false;

        } catch (ApiException $e) {
            $this->logger->critical($e->getMessage(), [$customer_id, $subscriptionArray]);
            return false;
        }
    }

	/**
	 * @param $email
	 * @param $name
	 *
	 * @return bool|object
	 * @since   2.0.0
	 */
	public function create_customer($email, $name) {

		$mollieApi = $this->mollieApi;

		$customerArray = [
			'email' => $email
		];

		if ($name) {
			$customerArray['name'] = $name;
		}

		try {
			return $mollieApi->customers->create($customerArray);
		} catch (ApiException $e) {
			$this->logger->critical($e->getMessage());
			return false;
		}

	}

	/**
	 * Cancel the specified subscription
	 *
	 * @param $subscriptionId
	 * @param null|string $customerId
	 *
	 * @return bool
	 */
	public function cancel_subscription($subscriptionId, $customerId=null) {

		$mollieApi = $this->mollieApi;
		$subscription = new Subscription();

		if(!$customerId) {
			$subscription->get_by(['subscription_id' => $subscriptionId]);

			if(empty($subscription)) {
				$this->logger->debug("Could not find subscription.", ['subscription_id' => $subscriptionId]);
				return false;
			}

			if($subscription->fields['status'] !== 'active') {
				$this->logger->debug("Subscription already canceled.", ['subscription_id' => $subscriptionId]);
				return false;
			}

			$customerId = $subscription->fields['customer_id'];
		}

		try {
			$customer = $mollieApi->customers->get($customerId);
			$mollieSubscription = $customer->cancelSubscription($subscriptionId);

			if($mollieSubscription) {

				$this->logger->info( "Subscription cancelled.", ['customer_id' => $customerId, 'subscription_id' => $subscriptionId]);
				$subscription->set_fields([
					'status' => 'cancelled'
				]);

				$subscription->save();

				return true;
			}

		} catch (ApiException $e) {
			$this->logger->critical($e->getMessage(), [$customerId, $subscriptionId]);
			return false;
		}

	}

	/**
	 * Register webhook using rest
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function register_webhook() {

	    // Payment webhook
		register_rest_route( 'kudos/v1', 'mollie/payment/webhook', [
			'methods' => 'POST',
			'callback' => [$this, 'rest_api_mollie_webhook'],
			'args' => [
				'id' => [
					'required' => true
				]
			]
		] );
	}

	/**
	 * REST route for checking API keys
	 *
	 * @since   2.0.0
	 */
	function register_api_key_check() {
		register_rest_route('kudos/v1', 'mollie/admin', [
			'methods'   => WP_REST_Server::READABLE,
			'callback'  => [$this, 'check_api_keys'],
			'args' => [
				'apiMode' => [
					'required' => true
				],
				'testKey',
				'liveKey'
			]
		]);
	}

	/**
	 * Check the Mollie Api key associated with the Api mode
	 *
	 * @param WP_REST_Request $request
	 *
	 * @since    1.1.0
	 */
	public function check_api_keys(WP_REST_Request $request) {

		$mode = sanitize_text_field($request['apiMode']);
		$apiKey = sanitize_text_field($request[$mode . 'Key']);

		// Check that the api key corresponds to the mode
		if(substr($apiKey, 0, 4) !== $mode) {
			/* translators: %s: API mode */
			wp_send_json_error( sprintf(__("%s API key should begin with \"%s\".", 'kudos-donations'), ucfirst($mode), $mode . '_'));
		}

		// Test api key
		$result = $this->check_api_key($apiKey);

		if($result) {
			update_option('_kudos_mollie_'.$mode.'_api_key', $apiKey);
			update_option('_kudos_mollie_api_mode', $mode);
			update_option('_kudos_mollie_connected', 1);
			/* translators: %s: API mode */
			wp_send_json_success(sprintf(__("%s API key connection was successful!", 'kudos-donations'), ucfirst($mode)));
		} else {
			/* translators: %s: API mode */
			update_option('_kudos_mollie_connected', 0);
			wp_send_json_error( sprintf(__("Error connecting with Mollie, please check the %s API key and try again.", 'kudos-donations'), ucfirst($mode)));
		}
	}

	/**
	 * Mollie webhook action
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_Error|WP_HTTP_Response|WP_REST_Response
	 * @since    1.0.0
	 */
	public function rest_api_mollie_webhook( WP_REST_Request $request ) {

	    // ID is case sensitive e.g: tr_Tb6UdQP523
		$id = sanitize_text_field($request->get_param( 'id' ));

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

		/** @var Payment $payment */
		$payment = $this->get_payment($id);

		if ( null === $payment ) {
			/**
			 *
			 * To not leak any information to malicious third parties, it is recommended
			 * to return a 200 OK response even if the ID is not known to your system.
			 *
			 * @link https://docs.mollie.com/guides/webhooks#how-to-handle-unknown-ids
			 */
			return $response;
		}

		$status = $payment->status;
		$sequence_type = $payment->sequenceType;
		$transaction_id = $payment->id;
		$order_id = $payment->metadata->order_id ?? generate_id('kdo_');
		$customer_id = $payment->customerId;
		$amount = $payment->amount;

		$this->logger->info('Webhook requested by Mollie.', ['transaction_id' => $id, 'status' => $status, 'sequence_type' => $sequence_type]);

		// Get transaction from database
		$transaction = new Transaction();
		$transaction->get_by([
			'order_id' => $order_id,
			'transaction_id' => $transaction_id
		], 'OR');

		// Update payment
		$transaction->set_fields([
			'status' => $status,
			'transaction_id' => $transaction_id,
			'customer_id' => $customer_id,
			'value' => $amount->value,
			'currency' => $amount->currency,
			'sequence_type' => $sequence_type,
			'method' => $payment->method,
			'mode' => $payment->mode,
			'subscription_id' => $payment->subscriptionId
		]);

		if($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks()) {

			$transaction->set_fields([
				'order_id' => $order_id
			]);

			// Get schedule processing for later
			if(class_exists('ActionScheduler')) {
				if ( false === as_next_scheduled_action( 'kudos_process_transaction_action', [ $transaction ] ) ) {
					$timestamp = strtotime('+1 minute');
					as_schedule_single_action( $timestamp, 'kudos_process_transaction_action', [ $transaction ] );
					$this->logger->debug( 'Action "kudos_process_transaction_action" scheduled', [
						'order_id' => $order_id,
						'datetime' => date_i18n( 'Y-m-d H:i:s', $timestamp )
					] );
				}
			} else {
				Kudos_Public::process_transaction($transaction);
			}

			// Set up recurring payment if sequence is first
			if($payment->hasSequenceTypeFirst()) {
				$kudos_mollie = new Kudos_Mollie();
				$kudos_mollie->create_subscription($transaction, $payment->mandateId, $payment->metadata->interval, $payment->metadata->years);
			}

		} elseif ($payment->hasRefunds()) {
			$this->logger->info('Payment (partially) refunded', [$transaction]);

			// Update transaction
			$refunded = $payment->getAmountRefunded();
			$remaining = $payment->getAmountRemaining();
			$transaction->set_fields([
				'refunds' => serialize(['refunded' => $refunded, 'remaining' => $remaining]),
			]);

			// Process refund
			$invoice = new Kudos_Invoice($transaction);
			$invoice->generate_refund();
		}

		$transaction->save();

		return $response;
	}
}