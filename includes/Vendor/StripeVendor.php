<?php
/**
 * Stripe payment vendor.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Vendor;

use IseardMedia\Kudos\Service\AbstractService;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

class StripeVendor extends AbstractService implements VendorInterface {

	public function register(): void {
		// TODO: Implement register() method.
	}

	public static function get_vendor_name(): string {
		return 'Stripe';
	}

	public static function get_vendor_slug(): string {
		return 'stripe';
	}

	public static function supports_recurring(): bool {
		return true;
	}

	public static function get_webhook_url(): string {
		return '';
	}

	public function check_api_keys( WP_REST_Request $request ): void {
		// TODO: Implement check_api_keys() method.
	}

	public function create_subscription( WP_Post $transaction, string $mandate_id, string $interval, string $years ) {
		// TODO: Implement create_subscription() method.
	}

	public function get_subscriptions( string $customer_id ) {
		// TODO: Implement get_subscriptions() method.
	}

	public function cancel_subscription( WP_Post $subscription ): bool {
		// TODO: Implement cancel_subscription() method.
	}

	public function refresh_api_connection( string $api_key ): bool {
		// TODO: Implement refresh_api_connection() method.
	}

	public function get_payment( string $vendor_payment_id ) {
		// TODO: Implement get_payment() method.
	}

	public function create_customer( string $email, string $name ) {
		// TODO: Implement create_customer() method.
	}

	public function get_customer( $vendor_customer_id ) {
		// TODO: Implement get_customer() method.
	}

	public function create_payment( array $payment_args, string $order_id, ?string $vendor_customer_id ): string {
		// TODO: Implement create_payment() method.
	}

	public function rest_webhook( WP_REST_Request $request ) {
		// TODO: Implement rest_webhook() method.
	}

	public function get_api_mode(): string {
		// TODO: Implement get_api_mode() method.
	}

	public function verify_connection( $data ): WP_REST_Response {
		// TODO: Implement verify_connection() method.
	}

	public function is_ready(): bool {
		// TODO: Implement is_ready() method.
	}
}