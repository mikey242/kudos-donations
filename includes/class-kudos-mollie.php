<?php

namespace Kudos\Mollie;

use Kudos\Transactions\Transaction;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;

class Mollie
{
	private $mollieApi;
	private $apiKey;

	public function __construct() {
		$this->mollieApi = new MollieApiClient();
		$this->apiKey = get_option('_mollie_api_key');
		if($this->apiKey) {
			$this->mollieApi->setApiKey($this->apiKey);
		}
	}

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

	public function getPayment($mollie_payment_id) {
		$mollieApi = $this->mollieApi;
		try {
			return $mollieApi->payments->get($mollie_payment_id);
		} catch (ApiException $e) {
			error_log($e->getMessage());
		}
		return false;
	}

	public function payment($value, $redirectUrl, $name=null, $email=null) {

		$mollieApi = $this->mollieApi;
		$order_id = time();
		setcookie('order_id', $order_id);
		$value = number_format($value, 2);

		try {
			$payment = $mollieApi->payments->create(
				[
					"amount" => [
						"currency" => "EUR",
						"value" => $value
					],
					"redirectUrl" => add_query_arg('kudos_order_id', base64_encode($order_id), $redirectUrl),
					"webhookUrl" => 'https://25b65ba4.ngrok.io/wp-json/kudos/v1/mollie',
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