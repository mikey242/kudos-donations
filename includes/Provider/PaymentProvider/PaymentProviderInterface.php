<?php
/**
 * PaymentProviderInterface
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

namespace IseardMedia\Kudos\Provider\PaymentProvider;

use IseardMedia\Kudos\Domain\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Provider\ProviderInterface;
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
	 * Refresh the local vendor settings.
	 */
	public function refresh(): bool;

	/**
	 * Shows the provider's admin notices.
	 */
	public function show_notices(): void;

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
	 * @param array             $payment_args Parameters to pass to the provider to create a payment.
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
	 */
	public function rest_webhook( WP_REST_Request $request ): WP_REST_Response;

	/**
	 * Method for handling a status change, generally called by webhook.
	 *
	 * @param string $vendor_payment_id The vendor's payment id.
	 */
	public function handle_status_change( string $vendor_payment_id ): void;

	/**
	 * Fetch the latest status from the vendor for the given local transaction, update it, and return it.
	 *
	 * @param int $transaction_id Local transaction ID.
	 * @return ?TransactionEntity The updated transaction, or null if not found.
	 */
	public function sync_transaction_status( int $transaction_id ): ?TransactionEntity;

	/**
	 * Returns true if the API is ready to communicate.
	 */
	public function is_vendor_ready(): bool;

	/**
	 * Returns true if a live-mode API key has been saved for this provider.
	 */
	public function has_live_key(): bool;

	/**
	 * Returns the setup steps this provider needs before it can take live payments.
	 *
	 * Rendered by the onboarding banner. Each step deep-links to a settings panel via its
	 * panel key. Providers with nothing to configure (e.g. demo) return an empty array.
	 *
	 * @return array<int, array{id: string, label: string, done: bool, panel: string}>
	 */
	public function get_onboarding_steps(): array;

	/**
	 * Returns the current vendor status derived from stored settings.
	 *
	 * Providers may add: methods (array), account (string), and other vendor-specific fields.
	 *
	 * @return array{ready: bool, recurring: bool, steps?: array, methods?: array, account?: string}
	 */
	public function get_status(): array;

	/**
	 * Returns the api mode.
	 */
	public function get_api_mode(): string;
}
