<?php
/**
 * Payment related functions.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\SubscriptionPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Service\Vendor\MollieVendor;
use IseardMedia\Kudos\Service\Vendor\VendorInterface;
use Psr\Log\LoggerInterface;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class PaymentService extends AbstractService {
	private MailerService $mailer_service;
	private LoggerInterface $logger;
	private VendorInterface $vendor;
	private SettingsService $settings;

	/**
	 * Payment service constructor.
	 *
	 * @see https://stackoverflow.com/questions/36853791/laravel-dynamic-dependency-injection-for-interface-based-on-user-input
	 *
	 * @param MailerService   $mailer_service Mailer service.
	 * @param LoggerInterface $logger Logger.
	 * @param VendorInterface $vendor Current vendor.
	 * @param SettingsService $settings Settings service.
	 */
	public function __construct(
		MailerService $mailer_service,
		LoggerInterface $logger,
		VendorInterface $vendor,
		SettingsService $settings
	) {
		$this->settings       = $settings;
		$this->vendor         = $vendor;
		$this->logger         = $logger;
		$this->mailer_service = $mailer_service;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		add_action( 'kudos_transaction_paid', [ $this, 'schedule_process_transaction' ] );
		add_action( 'kudos_process_transaction', [ $this, 'process_transaction' ] );
	}

	/**
	 * Returns current vendor class.
	 *
	 * @return VendorInterface
	 */
	public static function get_current_vendor_class(): string {
		return MollieVendor::class;
	}

	/**
	 * Checks if required api settings are saved before displaying button.
	 */
	public function is_api_ready(): bool {
		$settings  = $this->settings->get_current_vendor_settings();
		$connected = $settings['connected'] ?? false;
		$mode      = $settings['mode'] ?? '';
		$key       = $settings[ $mode . '_key' ] ?? null;

		if ( ! $connected || ! $key ) {
			return false;
		}

		return true;
	}

	/**
	 * Schedules processing of successful transaction.
	 *
	 * @param string $transaction_id The post id of the transaction.
	 */
	public static function schedule_process_transaction( string $transaction_id ): void {
		Utils::schedule_action(
			strtotime( '+1 minute' ),
			'kudos_process_transaction',
			[ $transaction_id ]
		);
	}

	/**
	 * Returns the name of the current vendor.
	 */
	public static function get_vendor_name(): string {
		return static::get_current_vendor_class()::get_vendor_name();
	}

	/**
	 * Check the vendor api key associated with the mode. Sends a JSON response.
	 *
	 * @param WP_REST_Request $request Instance of WP_REST_Request.
	 */
	public function check_api_keys( WP_REST_Request $request ): void {
		$this->vendor->check_api_keys( $request );
	}

	/**
	 * Processes the transaction. Used by action scheduler.
	 *
	 * @param int $transaction_id Transaction post id..
	 */
	public function process_transaction( int $transaction_id ): bool {
		$mailer = $this->mailer_service;

		$this->logger->debug( 'Processing paid transaction', [ 'transaction_id' => $transaction_id ] );

		// Get donor.
		$donor = get_post( get_post_meta( $transaction_id, 'donor_id', true ) );

		if ( get_post_meta( $donor->ID, 'email' ) ) {
			// Send email - email setting is checked in mailer.
			$mailer->send_receipt( $donor->ID, $transaction_id );
		}

		return true;
	}

	/**
	 * Handles the donation form submission.
	 *
	 * @param WP_REST_Request $request
	 */
	public function submit_payment( WP_REST_Request $request ): void {
		// Verify nonce.
		if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
			wp_send_json_error(
				[
					'message' => __( 'Request invalid.', 'kudos-donations' ),
					'nonce'   => $request->get_header( 'X-WP-Nonce' ),
				]
			);
		}

		$values = $request->get_body_params();

		// Check if bot filling tabs.
		if ( $this->is_bot( $values ) ) {
			wp_send_json_error( [ 'message' => __( 'Request invalid.', 'kudos-donations' ) ] );
		}

		$defaults = [
			'currency'         => 'EUR',
			'recurring_length' => 0,
			'redirect_url'     => get_site_url(),
			'name'             => null,
			'business_name'    => null,
			'email'            => null,
			'street'           => null,
			'postcode'         => null,
			'city'             => null,
			'country'          => null,
			'message'          => null,
			'campaign_id'      => null,
		];

		$args = wp_parse_args( $values, $defaults );

		// Add submit action and pass args.
		do_action( 'kudos_submit_payment', $args );

		// If email found, try to find an existing customer or create a new one.
		if ( $args['email'] ) {

			$donor_meta = [
				'mode'          => $this->vendor->get_api_mode(),
				'email'         => $args['email'],
				'name'          => $args['name'],
				'business_name' => $args['business_name'],
				'street'        => $args['street'],
				'postcode'      => $args['postcode'],
				'city'          => $args['city'],
				'country'       => $args['country'],
			];

			// Search for existing donor based on email and mode.
			$donor = DonorPostType::get_one_by_meta(
				[
					'email' => $args['email'],
					'mode'  => $this->vendor->get_api_mode(),
				]
			);

			// Create new customer with vendor if none found.
			if ( ! $donor ) {
				$customer                         = $this->vendor->create_customer( $args['email'], $args['name'] );
				$donor_meta['vendor_customer_id'] = $customer->id;
			}

			// Update or create donor.
			$donor = DonorPostType::save(
				[
					'ID' => $donor->ID ?? 0,
				],
				$donor_meta
			);
		}

		// Create the payment. If there is no customer ID it will be un-linked.
		$vendor_customer_id = $donor_meta['vendor_customer_id'] ?? null;
		$order_id           = Utils::generate_id( 'kdo_' );
		$url                = $this->vendor->create_payment( $args, $order_id, $vendor_customer_id );

		// Return checkout url if payment successfully created in Mollie.
		if ( $url ) {
			do_action( 'kudos_payment_submit_successful', $args );
			TransactionPostType::save(
				[],
				[
					'description'   => sprintf(
					/* translators: %s: The order id */
						__( 'Kudos Donation (%1$s) - %2$s', 'kudos-donations' ),
						$order_id,
						$args['value']
					),
					'order_id'      => $order_id,
					'donor_id'      => $donor->ID ?? null,
					'value'         => $args['value'],
					'currency'      => $args['currency'],
					'status'        => 'open',
					'mode'          => $this->vendor->get_api_mode(),
					'sequence_type' => 'true' === $args['recurring'] ? 'first' : 'oneoff',
					'campaign_id'   => (int) $args['campaign_id'],
					'message'       => $args['message'],
					'vendor'        => $this->vendor::get_vendor_slug(),
				]
			);

			wp_send_json_success( $url );
		}

		// If payment not created return an error message.
		wp_send_json_error(
			[
				'message' => __( 'Error creating Mollie payment. Please try again later.', 'kudos-donations' ),
			]
		);
	}

	/**
	 * Checks the provided honeypot field and logs request if bot detected.
	 *
	 * @param array $values Array of form value.
	 */
	public function is_bot( array $values ): bool {
		$time_diff = abs( $values['timestamp'] - time() );

		// Check if tabs completed too quickly.
		if ( $time_diff < 4 ) {
			$this->logger->info(
				'Bot detected, rejecting tabs.',
				[
					'reason'     => 'FormTab completed too quickly',
					'time_taken' => $time_diff,
				]
			);

			return true;
		}

		// Check if honeypot field completed.
		if ( ! empty( $values['donation'] ) ) {
			$this->logger->info(
				'Bot detected, rejecting tabs.',
				array_merge(
					[
						'reason' => 'Honeypot field completed',
					],
					$values
				)
			);

			return true;
		}

		return false;
	}

	/**
	 * Cancel the specified subscription.
	 *
	 * @param string $id subscription row ID.
	 */
	public function cancel_subscription( string $id ): bool {

		// Get subscription post from supplied row id.
		$subscription = get_post( $id );

		// Cancel subscription with vendor.
		$result = $subscription && $this->vendor->cancel_subscription( $subscription );

		if ( $result ) {
			// Update entity with canceled status.
			SubscriptionPostType::update_meta(
				$id,
				[
					'status' => 'cancelled',
				]
			);

			$this->logger->info(
				'Subscription cancelled.',
				[
					'id'              => $id,
					'subscription_id' => get_post_meta( $id, 'subscription_id', true ),
				]
			);

			return true;
		}

		return false;
	}

	/**
	 * Webhook handler. Passes request to rest_webhook method of current vendor.
	 *
	 * @param WP_REST_Request $request Request array.
	 * @return WP_Error|WP_REST_Response
	 */
	public function handle_webhook( WP_REST_Request $request ) {
		return $this->vendor->rest_webhook( $request );
	}
}
