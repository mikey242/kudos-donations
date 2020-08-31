<?php

namespace Kudos\Service;

use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Front\Front;
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
	 * @var string
	 */
	private $webHookUrl;


	/**
	 * Mollie constructor.
	 *
	 * @since      1.0.0
	 */
	public function __construct() {

		$this->mollieApi = new MollieApiClient();
		$this->apiMode = Settings::get_setting('mollie_api_mode');
		$this->apiKey = Settings::get_setting('mollie_'.$this->apiMode.'_api_key');
		$this->webHookUrl = WP_DEBUG ? 'https://b6ce7dfc6e4c.eu.ngrok.io/wp-json/kudos/v1/mollie/payment/webhook' : rest_url('kudos/v1/mollie/payment/webhook');

		if($this->apiKey) {
			try {
				$this->mollieApi->setApiKey($this->apiKey);
			} catch (ApiException $e) {
				$this->logger->critical($e->getMessage());
			}
		}

		parent::__construct();

	}

	/**
	 * The mollie class factory. In most cases this
	 * should be used instead of instantiating the object
	 * directly.
	 *
	 * @return MollieService
	 * @since   2.0.0
	 */
	public static function factory() {

		static $instance = false;

		if(!$instance) {
			$instance = new self;
		}

		return $instance;

	}

	/**
	 * Checks the provided api key by attempting to get associated payments
	 *
	 * @param string $apiKey
	 * @return bool
	 * @since      1.0.0
	 */
	public function test_api_connection( string $apiKey) {

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
	 * @param string $mollie_payment_id
	 * @return bool|Payment
	 * @since      1.0.0
	 */
	public function get_payment( string $mollie_payment_id ) {

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
	 * @param string $value
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
	public function create_payment( string $value, string $interval, string $years, string $redirectUrl, string $donation_label, $name=null, $email=null, $customerId=null) {

		$mollieApi = $this->mollieApi;
		$order_id = Utils::generate_id('kdo_');
		$currency = 'EUR';
		$value = number_format($value, 2);

		// Add order id query arg to return url if option to show message enabled
		if(get_option('_kudos_return_message_enable')) {
			$redirectUrl = add_query_arg('kudos_order_id', base64_encode($order_id), $redirectUrl);
			$redirectUrl = add_query_arg('kudos_token', wp_create_nonce('kudos_check_order-' . $order_id), $redirectUrl);
		}

		// Set payment frequency
		$frequency_text = Utils::get_frequency_name($interval);
		$sequenceType = ($interval === 'oneoff' ? 'oneoff' : 'first');

		// Create payment settings
		$paymentArray = [
			"amount" => [
				"currency" => $currency,
				"value" => $value
			],
			"redirectUrl" => $redirectUrl,
			"webhookUrl" => $this->webHookUrl,
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

		// Link payment to customer if specified
		if($customerId) {
			$paymentArray['customerId'] = $customerId;
		}

		try {
			$payment = $mollieApi->payments->create($paymentArray);

			$transaction = new TransactionEntity([
				'order_id' => $order_id,
				'customer_id' => $customerId,
				'value' => $value,
				'currency' => $currency,
				'status' => $payment->status,
				'mode' => $payment->mode,
				'sequence_type' => $payment->sequenceType,
				'donation_label' => $donation_label,
			]);

			$mapper = new MapperService(TransactionEntity::class);
			$mapper->save($transaction);

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
	 * @param string $customerId
	 * @return BaseCollection|bool
	 * @since   2.0.0
	 */
	public function get_subscriptions( string $customerId) {

		$mollieApi = $this->mollieApi;

		try {
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
	 * @param TransactionEntity $transaction
	 * @param string $mandateId
	 * @param string $interval
	 * @param string $years
	 *
	 * @return bool|object
	 * @since      2.0.0
	 */
	public function create_subscription( TransactionEntity $transaction, string $mandateId, string $interval, string $years ) {

        $mollieApi = $this->mollieApi;
        $customer_id = $transaction->customer_id;
        $startDate = date("Y-m-d", strtotime("+" . $interval));
        $currency = 'EUR';
        $value = number_format($transaction->value, 2);

        $subscriptionArray = [
            "amount" => [
                "value" => $value,
                "currency" => $currency
            ],
            "webhookUrl" => $this->webHookUrl,
	        "mandateId" => $mandateId,
            "interval" => $interval,
            "startDate" => $startDate,  // Disable for test mode
            "description" => sprintf(__('Kudos Subscription (%s) - %s', 'kudos-donations'), $interval, $transaction->order_id),
        ];

        if(WP_DEBUG) {
	        unset($subscriptionArray['startDate']);  // Disable for test mode
        }

        if($years && $years > 0) {
            $subscriptionArray["times"] = Utils::get_times_from_years($years, $interval);
        }

        try {
            $customer = $mollieApi->customers->get($customer_id);
            $mandate = $mollieApi->mandates->getFor($customer, $mandateId);

			if(!$mandate->status === 'pending' || !$mandate->status === 'valid') {
				$this->logger->error('Cannot create subscription as customer has no valid mandates.', [$customer_id]);
				return false;
			}

	        $subscription = $customer->createSubscription($subscriptionArray);

	        if($subscription) {
	        	$mapper = new MapperService(SubscriptionEntity::class);
		        $kudos_subscription = new SubscriptionEntity([
			        'transaction_id' => $transaction->transaction_id,
			        'customer_id' => $customer_id,
			        'frequency' => $interval,
			        'years' => $years,
			        'value' => $value,
			        'currency' => $currency,
			        'subscription_id' => $subscription->id,
			        'status' => $subscription->status
		        ]);
				$mapper->save($kudos_subscription);
		        return $subscription;
	        }

	        $this->logger->error('Failed to create subscription', [$transaction]);
	        return false;

        } catch (ApiException $e) {
            $this->logger->critical($e->getMessage(), [$customer_id, $subscriptionArray]);
            return false;
        }

    }

	/**
	 * @param string $email
	 * @param string $name
	 *
	 * @return bool|object
	 * @since   2.0.0
	 */
	public function create_customer( string $email, string $name ) {

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
	 * @param string $subscriptionId
	 * @param null|string $customerId
	 *
	 * @return bool
	 * @since   2.0.0
	 */
	public function cancel_subscription( string $subscriptionId, $customerId=null) {

		$mollieApi = $this->mollieApi;
		$mapper = new MapperService(SubscriptionEntity::class);

		/** @var SubscriptionEntity $subscription */
		$subscription = $mapper->get_one_by([ 'subscription_id' => $subscriptionId]);

		if(!$customerId) {
			if(empty($subscription)) {
				$this->logger->debug("Could not find subscription.", ['subscription_id' => $subscriptionId]);
				return false;
			}

			if($subscription->status !== 'active') {
				$this->logger->debug("Subscription already canceled.", ['subscription_id' => $subscriptionId]);
				return false;
			}

			$customerId = $subscription->customer_id;
		}

		try {
			$customer = $mollieApi->customers->get($customerId);
			$mollieSubscription = $customer->cancelSubscription($subscriptionId);

			if($mollieSubscription) {

				$this->logger->info( "Subscription cancelled.", ['customer_id' => $customerId, 'subscription_id' => $subscriptionId]);

				if(NULL !== $subscription) {
					$subscription->set_fields([
						'status' => 'cancelled'
					]);

					$mapper->save($subscription);
				}

				return true;
			}

			return false;

		} catch (ApiException $e) {
			$this->logger->critical($e->getMessage(), [$customerId, $subscriptionId]);
			return false;
		}

	}

	/**
	 * Check the Mollie Api key associated with the Api mode
	 *
	 * @param WP_REST_Request $request
	 *
	 * @since    1.1.0
	 */
	public function check_api_keys( WP_REST_Request $request) {

		update_option('_kudos_mollie_connected', 0);

		$mode = sanitize_text_field($request['apiMode']);
		$apiKey = sanitize_text_field($request[$mode . 'Key']);

		// Check that the api key corresponds to the mode
		if(substr($apiKey, 0, 4) !== $mode) {
			/* translators: %s: API mode */
			wp_send_json_error( sprintf(__("%s API key should begin with \"%s\".", 'kudos-donations'), ucfirst($mode), $mode . '_'));
		}

		// Test the api key
		$result = $this->test_api_connection($apiKey);

		if($result) {
			update_option('_kudos_mollie_'.$mode.'_api_key', $apiKey);
			update_option('_kudos_mollie_api_mode', $mode);
			update_option('_kudos_mollie_connected', 1);
			/* translators: %s: API mode */
			wp_send_json_success(sprintf(__("%s API key connection was successful!", 'kudos-donations'), ucfirst($mode)));
		} else {
			/* translators: %s: API mode */
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

		// Create action
		do_action('kudos_mollie_webhook', $payment);

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

		$this->logger->info('Webhook requested by Mollie.', ['transaction_id' => $id, 'status' => $payment->status, 'sequence_type' => $payment->sequenceType]);

		$transaction_id = $payment->id;
		$order_id = $payment->metadata->order_id ?? Utils::generate_id('kdo_');
		$amount = $payment->amount;

		// Get transaction from database
		$mapper = new MapperService( TransactionEntity::class);
		$transaction = $mapper->get_one_by([
			'order_id' => $order_id,
			'transaction_id' => $transaction_id
		], 'OR');

		// Create new transaction if none found
		if(NULL === $transaction) {
			$transaction = new TransactionEntity([
				'order_id' => $order_id
			]);
		}

		// Update payment
		$transaction->set_fields([
			'status' => $payment->status,
			'transaction_id' => $transaction_id,
			'customer_id' => $payment->customerId,
			'value' => $amount->value,
			'currency' => $amount->currency,
			'sequence_type' => $payment->sequenceType,
			'method' => $payment->method,
			'mode' => $payment->mode,
			'subscription_id' => $payment->subscriptionId
		]);

		if($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks()) {

			// Get schedule processing for later
			if(class_exists('ActionScheduler')) {
				if ( false === as_next_scheduled_action( 'kudos_process_transaction_action', [$order_id] ) ) {
					$timestamp = strtotime('+1 minute');
					as_schedule_single_action( $timestamp, 'kudos_process_transaction_action', [$order_id] );
					$this->logger->debug( 'Action "kudos_process_transaction_action" scheduled', [
						'datetime' => date_i18n( 'Y-m-d H:i:s', $timestamp )
					] );
				}
			} else {
				Front::process_transaction($order_id);
			}

			// Set up recurring payment if sequence is first
			if($payment->hasSequenceTypeFirst()) {
				$this->logger->debug('Creating subscription', [$transaction]);
				$this->create_subscription($transaction, $payment->mandateId, $payment->metadata->interval, $payment->metadata->years);
			}

		} elseif ($payment->hasRefunds()) {
			$this->logger->info('Payment (partially) refunded', [$transaction]);

			// Update transaction
			$refunded = $payment->getAmountRefunded();
			$remaining = $payment->getAmountRemaining();
			$transaction->set_fields([
				'refunds' => serialize(['refunded' => $refunded, 'remaining' => $remaining]),
			]);

			do_action('kudos_mollie_refund', $order_id);
		}

		// Save transaction to database
		$mapper->save($transaction);

		// Return response to Mollie
		return $response;
	}
}