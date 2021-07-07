<?php

namespace Kudos\Service\Vendor;

use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

interface VendorInterface {

	/**
	 * Returns the name of the vendor.
	 *
	 * @return string
	 */
	public static function get_vendor_name():string;

	/**
	 * Check the vendor api key key associated with the mode. Sends a JSON response.
	 *
	 * @return mixed
	 */
	public function check_api_keys();

	/**
	 * @param TransactionEntity $transaction ,
	 * @param string $mandate_id ,
	 * @param string $interval ,
	 * @param string $years
	 */
	public function create_subscription(
		TransactionEntity $transaction,
		string $mandate_id,
		string $interval,
		string $years
	);

	/**
	 * Returns all subscriptions for customer
	 *
	 * @param string $customer_id customer id.
	 *
	 * @since   2.0.0
	 */
	public function get_subscriptions( string $customer_id );

	/**
	 * Cancel the specified subscription
	 *
	 * @param SubscriptionEntity $subscription subscription row id.
	 *
	 * @return bool
	 */
	public function cancel_subscription( SubscriptionEntity $subscription ): bool;

	/**
	 * Checks the provided api key by attempting to get associated payments
	 *
	 * @param string $api_key API key to test.
	 *
	 * @return bool
	 * @since      1.0.0
	 */
	public function refresh_api_connection( string $api_key ): bool;

	/**
	 * Gets specified payment
	 *
	 * @param string $mollie_payment_id Mollie payment id.
	 */
	public function get_payment( string $mollie_payment_id );

	/**
	 * Returns the vendors webhook url.
	 *
	 * @return string
	 */
	public static function get_webhook_url(): string;

	/**
	 * Create a customer.
	 *
	 * @param string $email Donor email address.
	 * @param string $name Donor name.
	 */
	public function create_customer( string $email, string $name );

	/**
	 * Get the customer
	 *
	 * @param $customer_id
	 */
	public function get_customer( $customer_id );

	/**
	 * Creates a payment and returns it as an object
	 *
	 * @param array $payment_array Parameters to pass to mollie to create a payment.
	 */
	public function create_payment( array $payment_array );

	/**
	 * Vendor webhook action
	 *
	 * @param WP_REST_Request $request Request array.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_webhook( WP_REST_Request $request );

	/**
	 * Vendor API mode
	 *
	 * @return string
	 */
	public function get_api_mode(): string;

	/**
	 * Returns the vendor name as a string
	 *
	 * @return string
	 */
	public function __toString(): string;
}
