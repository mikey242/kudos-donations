<?php

namespace Kudos;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
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
		$this->apiMode = carbon_get_theme_option('kudos_mollie_api_mode');
		$this->apiKey = carbon_get_theme_option('kudos_mollie_'.$this->apiMode.'_api_key');
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
	public function checkApiKey($apiKey) {

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
	public function getPayment($mollie_payment_id) {
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
	 * @since      1.0.0

	 * @param string $redirectUrl
	 * @param string|null $name
	 * @param string|null $email
	 * @return bool|object
	 */
	public function payment($value, $redirectUrl, $name=null, $email=null) {

		$mollieApi = $this->mollieApi;
		$order_id = time();
		$currency = 'EUR';
		$value = number_format($value, 2);

		// Add order id if option to show message enabled
		if(carbon_get_theme_option('kudos_return_message_enable')) {
			$redirectUrl = add_query_arg('kudos_order_id', base64_encode($order_id), $redirectUrl);
			$redirectUrl = add_query_arg('_wpnonce', wp_create_nonce('check_kudos_order-' . $order_id), $redirectUrl);
		}

		try {
			$payment = $mollieApi->payments->create(
				[
					"amount" => [
						"currency" => $currency,
						"value" => $value
					],
					"redirectUrl" => $redirectUrl,
					"webhookUrl" => rest_url('kudos/v1/mollie/webhook'),
					/* translators: %s: The order id */
					"description" => sprintf(__("Kudos Payment - %s", 'kudos-donations'), $order_id),
					'metadata' => [
						'order_id' => $order_id,
						'email' => $email,
						'name' => $name
					]
				]
			);

			$transaction = $this->transaction;
			$transaction->create_record($order_id, $value, $currency, $payment->status, $payment->sequenceType, $email, $name);

			return $payment;

		} catch (ApiException $e) {
			$this->logger->log($e->getMessage(), 'CRITICAL');
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
		register_rest_route( 'kudos/v1', 'mollie/webhook', [
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
			wp_send_json_error( sprintf(__("%s API key should begin with \"%s\".", 'kudos-donations'), ucfirst($mode), $mode . '_'));
		}

		// Test api key
		$result = $this->checkApiKey($apiKey);

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

		$payment = $this->getPayment($id);

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
		$this->transaction->update_record($order_id, $transaction_id, $status, $payment->method);

		// Send email receipt on success
		$mailer = new Kudos_Mailer();
		if($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks()) {

			// Get transaction
			$transaction = $this->transaction->get_transaction($order_id);

			// Create invoice
			$this->invoice->generate_invoice($transaction);

			// Send email - email setting is checked in mailer
			if($transaction->email) {
				$mailer->send_invoice($transaction);
			}
		}

		/* translators: %s: Mollie */
		$note = sprintf(__( 'Webhook requested by %s.', 'kudos-donations' ),'Mollie');
		$this->logger->log($note, 'INFO', ['order_id' => $order_id, 'status' => $status]);

		return $response;
	}
}