<?php

namespace Kudos\Mollie;

use Kudos\Transactions\Transaction;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Payment;

class Mollie
{
	private $mollieApi;
	private $apiKey;
	private $apiMode;

	/**
	 * Mollie constructor.
	 */
	public function __construct() {
		$this->mollieApi = new MollieApiClient();
		$this->apiMode = get_option('_kudos_mollie_api_mode');
		$this->apiKey = get_option('_kudos_mollie_'.$this->apiMode.'_api_key');
		if($this->apiKey) {
			try {
				$this->mollieApi->setApiKey($this->apiKey);
			} catch (ApiException $e) {
				error_log($e->getMessage());
			}
		}
	}

	/**
	 * @param $apiKey
	 *
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
	 * @param $mollie_payment_id
	 *
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
	 * @param string $value
	 * @param string $redirectUrl
	 * @param string|null $name
	 * @param string|null $email
	 *
	 * @return bool|object
	 */
	public function payment($value, $redirectUrl, $name=null, $email=null) {

		$mollieApi = $this->mollieApi;
		$order_id = time();
		setcookie('order_id', $order_id);
		$value = number_format($value, 2);

		// Add order id if option to show message enabled
		if(get_option('_kudos_return_message_enable')) {
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
					"webhookUrl" => 'https://927ba6df.ngrok.io/wp-json/kudos/v1/mollie',
//					"webhookUrl" => rest_url('kudos/v1/mollie'),
					"description" => "Kudos Payment - $order_id",
					'metadata' => [
						'order_id' => $order_id,
						'email' => $email,
						'name' => $name
					]
				]
			);

			$transaction = new Transaction();
			$transaction->create_record($order_id, $value, $email, $name);

			return $payment;

		} catch (ApiException $e) {
			error_log($e->getMessage() . 'redirectUrl: ');
			return false;
		}

	}
}