<?php

namespace Kudos\Mollie;

use Kudos\Transactions\Transaction;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Payment;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;

class Mollie
{
	private $mollieApi;
	private $apiKey;
	private $apiMode;
	private $transaction;

	/**
	 * Mollie constructor.
	 *
	 * @since      1.0.0
	 */
	public function __construct() {
		$this->transaction = new Transaction();
		$this->mollieApi = new MollieApiClient();
		$this->apiMode = carbon_get_theme_option('kudos_mollie_api_mode');
		$this->apiKey = carbon_get_theme_option('kudos_mollie_'.$this->apiMode.'_api_key');
		if($this->apiKey) {
			try {
				$this->mollieApi->setApiKey($this->apiKey);
			} catch (ApiException $e) {
				error_log($e->getMessage());
			}
		}
	}

	/**
	 * Checks the provided api key by attempting to get associated payments
	 *
	 * @since      1.0.0
	 * @param $apiKey
	 * @return bool
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
			error_log($e->getMessage());
			return false;
		}
		return true;
	}

	/**
	 * Gets specified payment
	 *
	 * @since      1.0.0
	 * @param $mollie_payment_id
	 * @return bool|Payment
	 */
	public function getPayment($mollie_payment_id) {
		$mollieApi = $this->mollieApi;
		try {
			return $mollieApi->payments->get($mollie_payment_id);
		} catch (ApiException $e) {
			error_log($e->getMessage());
		}
		return false;
	}

	/**
	 * Creates a payment and returns it as an object
	 *
	 * @since      1.0.0
	 * @param string $value
	 * @param string $redirectUrl
	 * @param string|null $name
	 * @param string|null $email
	 * @return bool|object
	 */
	public function payment($value, $redirectUrl, $name=null, $email=null) {

		$mollieApi = $this->mollieApi;
		$order_id = time();
		setcookie('kudos_order_id', $order_id);
		$value = number_format($value, 2);

		// Add order id if option to show message enabled
		if(carbon_get_theme_option('kudos_return_message_enable')) {
			$redirectUrl = add_query_arg('kudos_order_id', base64_encode($order_id), $redirectUrl);
		}

		try {
			$payment = $mollieApi->payments->create(
				[
					"amount" => [
						"currency" => "EUR",
						"value" => $value
					],
					"redirectUrl" => $redirectUrl,
//					"webhookUrl" => 'http://1db3a710.ngrok.io/wp-json/kudos/v1/mollie',
					"webhookUrl" => rest_url('kudos/v1/mollie'),
					"description" => "Kudos Payment - $order_id",
					'metadata' => [
						'order_id' => $order_id,
						'email' => $email,
						'name' => $name
					]
				]
			);

			$transaction = $this->transaction;
			$transaction->create_record($order_id, $value, $email, $name);

			return $payment;

		} catch (ApiException $e) {
			error_log($e->getMessage());
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
		register_rest_route( 'kudos/v1', 'mollie/', [
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
	 * Mollie webhook action
	 *
	 * @since    1.0.0
	 * @param WP_REST_Request $request
	 * @return mixed|WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function rest_api_mollie_webhook( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		error_log('Webhook received with ID: ' . $id);

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
		$this->transaction->update_record($order_id, $transaction_id, $payment->status, $payment->method);

		// Add note.
		$note = sprintf(
		/* translators: %s: Mollie */
			__( 'Webhook requested by %s.', 'kudos' ),
			__( 'Mollie', 'kudos' )
		);

		error_log( $note );

		return $response;
	}
}