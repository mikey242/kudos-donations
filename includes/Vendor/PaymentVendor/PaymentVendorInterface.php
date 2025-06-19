<?php

namespace IseardMedia\Kudos\Vendor\PaymentVendor;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

interface PaymentVendorInterface
{
    /**
     * Returns the name of the vendor.
     *
     * @return string
     */
    public static function get_name(): string;

	/**
	 * Gets the vendor slug for identification.
	 *
	 * @return string
	 */
	public static function get_slug(): string;

    /**
     * Returns true if vendor supports recurring payments.
     *
     * @return bool
     */
    public static function recurring_enabled(): bool;

	/**
	 * Refresh the local vendor settings.
	 */
	public function refresh();

    /**
     * Cancel the specified subscription.
     *
     * @param array $subscription subscription row.
     *
     * @return bool
     */
    public function cancel_subscription( array $subscription): bool;

    /**
     * Create a customer.
     *
     * @param string $email Donor email address.
     * @param string $name Donor name.
     */
    public function create_customer(string $email, string $name);

    /**
     * Creates a payment and returns it as an object.
     *
     * @param array $payment_args Parameters to pass to mollie to create a payment.
     * @param array $transaction The transaction entity array.
     *
     * @return string|false
     */
    public function create_payment(array $payment_args, array $transaction);

	/**
	 * Refunds the provided transaction.
	 *
	 * @param int $post_id The post ID of the transaction to refund.
	 */
	public function refund(int $post_id): bool;

    /**
     * Vendor webhook action.
     *
     * @param WP_REST_Request $request Request array.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function rest_webhook(WP_REST_Request $request);

	/**
	 * Returns true is the API is ready to communicate.
	 */
	public function is_ready(): bool;

	/**
	 * Returns the api mode.
	 *
	 * @return string
	 */
	public function get_api_mode(): string;
}
