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
	}

	public function payment($value, $email, $redirectUrl) {

		$mollieApi = $this->mollieApi;
		$transaction = new Transaction();
		$order_id = time();
		setcookie('order_id', $order_id);
		$value = number_format($value, 2);
		$transaction->create_record($order_id, $email, $value);

		try {
			return $mollieApi->payments->create(
				[
					"amount" => [
						"currency" => "EUR",
						"value" => $value
					],
					"redirectUrl" => $redirectUrl . '?kudos_order_id=' . $order_id,
					"webhookUrl" => 'https://9486fba9.ngrok.io/wp-json/kudos/v1/mollie',
//					"webhookUrl" => rest_url('kudos/v1/mollie'),
					"description" => "Kudos Payment - $order_id",
					'metadata' => [
						'order_id' => $order_id
					]
				]
			);

		} catch (ApiException $e) {
			error_log($e->getMessage());
			return false;
		}

	}
}