<?php

namespace Kudos;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Customer;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Subscription;
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
	 * @var Kudos_Transaction
	 */
	private $transaction;
	/**
	 * @var Kudos_Invoice
	 */
	private $invoice;
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
		$this->transaction = new Kudos_Transaction();
		$this->invoice = new Kudos_Invoice();
		$this->mollieApi = new MollieApiClient();
		$this->apiMode = get_option('_kudos_mollie_api_mode');
		$this->apiKey = get_option('_kudos_mollie_'.$this->apiMode.'_api_key');
		if($this->apiKey) {
			try {
				$this->mollieApi->setApiKey($this->apiKey);
			} catch (ApiException $e) {
				$this->logger->log($e->getMessage(), 'CRITICAL');
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
			$this->logger->log($e->getMessage(), 'CRITICAL');
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
			$this->logger->log($e->getMessage(), 'CRITICAL');
		}
		return false;
	}

    /**
     * Creates a payment and returns it as an object
     *
     * @param $value
     * @param string $redirectUrl
     * @param string|null $payment_frequency
     * @param string|null $name
     * @param string|null $email
     *
     * @param $customerId
     *
     * @return bool|object
     * @since      1.0.0
     */
	public function create_payment($value, $payment_frequency, $redirectUrl, $name=null, $email=null, $customerId=null) {

		$mollieApi = $this->mollieApi;
		$order_id = 'kdo_'.time();
		$currency = 'EUR';
		$value = number_format($value, 2);

		// Add order id if option to show message enabled
		if(get_option('_kudos_return_message_enable')) {
			$redirectUrl = add_query_arg('kudos_order_id', base64_encode($order_id), $redirectUrl);
			$redirectUrl = add_query_arg('_wpnonce', wp_create_nonce('check_kudos_order-' . $order_id), $redirectUrl);
		}

		// Set payment frequency
		switch ($payment_frequency) {
            case "12 months":
                $frequency_text = __('Yearly', 'kudos-donations');
                $sequenceType = 'first';
                break;
            case "1 month":
                $frequency_text = __('Monthly', 'kudos-donations');
                $sequenceType = 'first';
                break;
            case "oneoff":
            default:
                $frequency_text = __('One off', 'kudos-donations');
                $sequenceType = 'oneoff';
        }

		$paymentArray = [
			"amount" => [
				"currency" => $currency,
				"value" => $value
			],
			"redirectUrl" => $redirectUrl,
//			"webhookUrl" => rest_url('kudos/v1/mollie/payment/webhook'),
            "sequenceType" => $sequenceType,
			"webhookUrl" => 'http://d38699244220.ngrok.io/wp-json/kudos/v1/mollie/payment/webhook',
			/* translators: %s: The order id */
			"description" => sprintf(__("Kudos Donation (%s) - %s", 'kudos-donations'), $frequency_text, $order_id),
			'metadata' => [
				'order_id' => $order_id,
				'payment_frequency' => $payment_frequency,
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

			$transaction = $this->transaction;
			$transaction->insert_transaction($order_id, $customerId, $value, $currency, $payment->status, $payment->sequenceType);

			return $payment;

		} catch (ApiException $e) {
			$this->logger->log($e->getMessage(), 'CRITICAL');
			return false;
		}

	}

	/**
	 * Create a subscription
	 *
	 * @param object $transaction
	 * @param $interval
	 * @param null $times
	 *
	 * @return bool|object
	 * @since      1.1.0
	 */
	public function create_subscription($transaction, $interval, $times=null) {

        $mollieApi = $this->mollieApi;
        $customer_id = $transaction->customer_id;
        $k_subscription_id = 'kds_'.time();
        $startDate = date("Y-m-d", strtotime("+" . $interval));
        $currency = 'EUR';
        $value = number_format($transaction->value, 2);

        $subscriptionArray = [
            "amount" => [
                "value" => $value,
                "currency" => $currency
            ],
            "interval" => $interval,
            "startDate" => $startDate,
            "description" => sprintf(__('Kudos Subscription (%s) - %s', 'kudos-donations'), $interval, $k_subscription_id),
//            "webhookUrl" => rest_url('kudos/v1/mollie/subscription/webhook'),
            "webhookUrl" => 'http://d38699244220.ngrok.io/wp-json/kudos/v1/mollie/subscription/webhook',
            "metadata" => [
                "subscription_id" => $k_subscription_id
            ]
        ];

        if($times) {
            $subscriptionArray["times"] = $times;
        }

        try {
            /** @var Customer $customer */
            $customer = $mollieApi->customers->get($customer_id);
	        $subscription = $customer->createSubscription($subscriptionArray);

	        if($subscription) {
		        $kudos_subscription = new Kudos_Subscription();
		        $kudos_subscription->insert_subscription($transaction->transaction_id, $customer_id, $interval, $value, $currency, $k_subscription_id, $subscription->id, $subscription->status);
		        return $subscription;
	        }

	        return false;

        } catch (ApiException $e) {
            $this->logger->log($e->getMessage(), 'CRITICAL', [$customer_id, $subscriptionArray]);
            return false;
        }
    }

	/**
	 * @param $email
	 * @param $name
	 *
	 * @return bool|object
	 * @since   1.1.0
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
			$this->logger->log($e->getMessage(), 'CRITICAL');
			return false;
		}

	}

	public function cancel_subscription($customerId, $subscriptionId) {

		$mollieApi = $this->mollieApi;

		try {
			$customer = $mollieApi->customers->get($customerId);
			return $customer->cancelSubscription($subscriptionId);
		} catch (ApiException $e) {
			$this->logger->log($e->getMessage(), 'CRITICAL', [$customerId, $subscriptionId]);
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

		// Subscription webhook
        register_rest_route( 'kudos/v1', 'mollie/subscription/webhook', [
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
	 * @since   1.1.0
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

		// Update payment.
		$order_id = $payment->metadata->order_id;
		$transaction_id = $payment->id;
		$status = $payment->status;
        $sequence_type = $payment->sequenceType;
		$this->transaction->update_transaction($order_id, [
			'status' => $status,
			'transaction_id' => $transaction_id,
			'method' => $payment->method
		]);

		// Send email receipt on success
		$mailer = new Kudos_Mailer();
		if($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks()) {

			// Get transaction
			$transaction = $this->transaction->get_transaction_by(['order_id' => $order_id]);

			// Create invoice
			$this->invoice->generate_invoice($transaction);

			if($transaction->email) {

                // Send email - email setting is checked in mailer
				$mailer->send_invoice($transaction);

                // Set up recurring payment if sequence is first
                if($sequence_type === 'first') {
                    return $this->create_subscription($transaction, $payment->metadata->payment_frequency);
                }
			}
		}

		/* translators: %s: Mollie */
		$note = sprintf(__( 'Webhook requested by %s.', 'kudos-donations' ),'Mollie');
		$this->logger->log($note, 'INFO', ['order_id' => $order_id, 'status' => $status, 'sequence_type' => $sequence_type]);

		return $response;
	}
}