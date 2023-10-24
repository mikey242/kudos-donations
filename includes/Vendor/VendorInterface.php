<?php

namespace IseardMedia\Kudos\Vendor;

use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

interface VendorInterface
{
    /**
     * Returns the name of the vendor.
     *
     * @return string
     */
    public static function get_vendor_name(): string;

	/**
	 * Gets the vendor slug for identification.
	 *
	 * @return string
	 */
	public static function get_vendor_slug(): string;

    /**
     * Returns true if vendor supports recurring payments.
     *
     * @return bool
     */
    public static function supports_recurring(): bool;

    /**
     * Returns the vendors webhook url.
     *
     * @return string
     */
    public static function get_webhook_url(): string;

	/**
	 * Check the vendor connection.
	 *
	 * @param mixed $data
	 *
	 * @return WP_REST_Response
	 */
	public function verify_connection($data): WP_REST_Response;

    /**
     * @param WP_Post $transaction ,
     * @param string $mandate_id ,
     * @param string $interval ,
     * @param int $years
     */
    public function create_subscription(
        WP_Post $transaction,
        string $mandate_id,
        string $interval,
        int $years
    );

    /**
     * Returns all subscriptions for customer.
     *
     * @param string $customer_id customer id.
     *
     * @since   2.0.0
     */
    public function get_subscriptions(string $customer_id);

    /**
     * Cancel the specified subscription.
     *
     * @param WP_Post $subscription subscription row id.
     *
     * @return bool
     */
    public function cancel_subscription( WP_Post $subscription): bool;

    /**
     * Checks the provided api key by attempting to get associated payments.
     *
     * @param string $api_key API key to test.
     *
     * @return bool
     * @since      1.0.0
     */
    public function refresh_api_connection(string $api_key): bool;

    /**
     * Gets specified payment.
     *
     * @param string $vendor_payment_id Vendor payment id.
     */
    public function get_payment(string $vendor_payment_id);

    /**
     * Create a customer.
     *
     * @param string $email Donor email address.
     * @param string $name Donor name.
     */
    public function create_customer(string $email, string $name);

    /**
     * Get the customer.
     *
     * @param $vendor_customer_id
     */
    public function get_customer($vendor_customer_id);

    /**
     * Creates a payment and returns it as an object.
     *
     * @param array $payment_args Parameters to pass to mollie to create a payment.
     * @param int $transaction_id Transaction ID.
     * @param string|null $vendor_customer_id ID of customer the payment is for.
     *
     * @return string
     */
    public function create_payment(array $payment_args, int $transaction_id, ?string $vendor_customer_id): string;

    /**
     * Vendor webhook action.
     *
     * @param WP_REST_Request $request Request array.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function rest_webhook(WP_REST_Request $request);

    /**
     * Vendor API mode.
     *
     * @return string
     */
    public function get_api_mode(): string;

	/**
	 * Returns true is the API is ready to communicate.
	 */
	public function is_ready(): bool;
}
