<?php

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;

class Mollie
{
	private $mollieApi;
	private $apiKey;

	public function __construct() {
		$this->mollieApi = new MollieApiClient();
		$this->apiKey = carbon_get_theme_option('mollie_api_key');
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
			return false;
		}
		return true;
	}

	public function payment($value, $email) {

		$mollieApi = $this->mollieApi;
		$value = number_format($value, 2);

		try {
			$payment = $mollieApi->payments->create(
				[
					"amount" => [
						"currency" => "EUR",
						"value" => $value
					],
					"redirectUrl" => "http://diamanttheater.test",
					"description" => "Kudos Payment",
				]
			);

//			header("Location: " . $payment->getCheckoutUrl(), true, 303);
			return $payment;

		} catch (ApiException $e) {
			error_log($e->getMessage());
			return false;
		}

	}
}