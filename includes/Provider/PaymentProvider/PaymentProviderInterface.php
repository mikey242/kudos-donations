<?php
/**
 * PaymentProviderInterface
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

namespace IseardMedia\Kudos\Provider\PaymentProvider;

use IseardMedia\Kudos\Domain\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Provider\ProviderInterface;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

interface PaymentProviderInterface extends ProviderInterface {
	/**
	 * Returns the name of the vendor.
	 */
	public static function get_name(): string;

	/**
	 * Gets the vendor slug for identification.
	 */
	public static function get_slug(): string;

	/**
	 * Checks the status of provided payment
	 *
	 * @param string $payment_id The vendor's payment id.
	 */
	public function check_payment_status( string $payment_id ): ?string;

	/**
	 * Returns true if vendor supports recurring payments.
	 */
	public static function recurring_enabled(): bool;

	/**
	 * Refresh the local vendor settings.
	 */
	public function refresh(): bool;

	/**
	 * Cancel the specified subscription.
	 *
	 * @param SubscriptionEntity $subscription subscription row.
	 */
	public function cancel_subscription( SubscriptionEntity $subscription ): bool;

	/**
	 * Create a customer.
	 *
	 * @param string $email Donor email address.
	 * @param string $name Donor name.
	 * @return mixed
	 */
	public function create_customer( string $email, string $name );

	/**
	 * Creates a payment and returns it as an object.
	 *
	 * @param array             $payment_args Parameters to pass to mollie to create a payment.
	 * @param TransactionEntity $transaction The transaction entity array.
	 * @param ?string           $vendor_customer_id The vendors customer id.
	 * @return string|false
	 */
	public function create_payment( array $payment_args, TransactionEntity $transaction, ?string $vendor_customer_id = null );

	/**
	 * Refunds the provided transaction.
	 *
	 * @param int $entity_id The entity ID of the transaction to refund.
	 */
	public function refund( int $entity_id ): bool;

	/**
	 * Vendor webhook action.
	 *
	 * @param WP_REST_Request $request Request array.
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_webhook( WP_REST_Request $request );

	/**
	 * Method for handling a status change, generally called by webhook.
	 *
	 * @param string $vendor_payment_id The vendor's payment id.
	 */
	public function handle_status_change( string $vendor_payment_id ): void;

	/**
	 * Returns true is the API is ready to communicate.
	 */
	public function is_vendor_ready(): bool;

	/**
	 * Returns the status of the vendor in an array.
	 *
	 * @returns array{
	 *     ready: bool,  // Result of is_vendor_ready.
	 *     text: string  // The message to display to the end user.
	 * }
	 */
	public function vendor_status(): array;

	/**
	 * Returns the api mode.
	 */
	public function get_api_mode(): string;
}
