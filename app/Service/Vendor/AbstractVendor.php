<?php

namespace Kudos\Service\Vendor;

use Kudos\Entity\TransactionEntity;
use Kudos\Service\AbstractService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

abstract class AbstractVendor extends AbstractService {

	/**
	 * Namespace used for registering the routes
	 */
	const REST_NAMESPACE = 'kudos/v1';

	/**
	 * This is the name of the vendor as it will appear in the logs
	 */
	const VENDOR_NAME = 'Mollie';

	/**
	 * The route used for the webhook
	 */
	const WEBHOOK_ROUTE = '/mollie/payment/webhook';

	/**
	 * New payment rest route
	 */
	const PAYMENT_ROUTE = '/mollie/payment/create';

	/**
	 * Rest route used for checking if api key is valid
	 */
	const TEST_API = '/mollie/check-api';

	/**
	 * @param TransactionEntity $transaction,
	 * @param string $mandate_id,
	 * @param string $interval,
	 * @param string $years
	 */
	abstract public function create_subscription(
		TransactionEntity $transaction,
		string $mandate_id,
		string $interval,
		string $years
	);

	/**
	 * Returns all subscriptions for customer
	 *
	 * @param string $customer_id customer id.
	 * @since   2.0.0
	 */
	abstract public function get_subscriptions( string $customer_id );

	/**
	 * Cancel the specified subscription
	 *
	 * @param string $subscription_id subscription id.
	 * @param null|string $customer_id customer id.
	 * @return bool
	 */
	abstract public function cancel_subscription( string $subscription_id, $customer_id = null ): bool;

	/**
	 * Checks the provided api key by attempting to get associated payments
	 *
	 * @param string $api_key API key to test.
	 *
	 * @return bool
	 * @since      1.0.0
	 */
	abstract public function test_api_connection( string $api_key ): bool;

	/**
	 * Gets specified payment
	 *
	 * @param string $mollie_payment_id Mollie payment id.
	 */
	abstract public function get_payment( string $mollie_payment_id );

	/**
	 * Create a Mollie customer.
	 *
	 * @param string $email Donor email address.
	 * @param string $name Donor name.
	 */
	abstract public function create_customer( string $email, string $name );

	/**
	 * Get the customer from Mollie
	 *
	 * @param $customer_id
	 */
	abstract public function get_customer( $customer_id );

	/**
	 * Creates a payment and returns it as an object
	 *
	 * @param array $payment_array Parameters to pass to mollie to create a payment.
	 */
	abstract public function create_payment( array $payment_array );

	/**
	 * Mollie webhook action
	 *
	 * @param WP_REST_Request $request Request array.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	abstract public function rest_webhook( WP_REST_Request $request );

	/**
	 * Returns the vendor name as a string
	 *
	 * @return string
	 */
	public function __toString(): string {

		return $this::VENDOR_NAME;

	}
}