<?php

namespace Kudos\Service;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\Vendor\MollieVendor;
use Kudos\Service\Vendor\VendorInterface;
use Mollie\Api\Resources\Payment;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class PaymentService {

	/**
	 * @var VendorInterface
	 */
	private $vendor;
	/**
	 * @var \Kudos\Service\MailerService
	 */
	private $mailer_service;
	/**
	 * @var \Kudos\Service\MapperService
	 */
	private $mapper_service;
	/**
	 * @var \Kudos\Service\LoggerService
	 */
	private $logger;

	/**
	 * Payment service constructor.
	 */
	public function __construct(
		MapperService $mapper_service,
		MailerService $mailer_service,
		LoggerService $logger_service
	) {

		$vendor               = $this::get_current_vendor_class();
		$this->vendor         = new $vendor( $mapper_service, $logger_service );
		$this->mapper_service = $mapper_service;
		$this->mailer_service = $mailer_service;
		$this->logger         = $logger_service;

	}

	/**
	 * Returns current vendor class.
	 *
	 * @return VendorInterface
	 */
	private static function get_current_vendor_class(): string {

		switch ( Settings::get_setting( 'payment_vendor' ) ) {
			default:
				return MollieVendor::class;
		}

	}

	/**
	 * Checks if required api settings are saved before displaying button.
	 *
	 * @return bool
	 */
	public static function is_api_ready(): bool {

		$settings  = Settings::get_current_vendor_settings();
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
	 * @param string $order_id
	 */
	public static function schedule_process_transaction( string $order_id ) {

		Utils::schedule_action(
			strtotime( '+1 minute' ),
			'kudos_process_' . strtolower( self::get_vendor_name() ) . '_transaction',
			[ $order_id ] );

	}

	/**
	 * Returns the name of the current vendor.
	 *
	 * @return string
	 */
	public static function get_vendor_name(): string {

		return static::get_current_vendor_class()::get_vendor_name();

	}

	/**
	 * Check the vendor api key associated with the mode. Sends a JSON response.
	 */
	public function check_api_keys() {

		$this->vendor->check_api_keys();

	}

	/**
	 * Processes the transaction. Used by action scheduler.
	 *
	 * @param string $order_id Kudos order id.
	 *
	 * @return bool
	 */
	public function process_transaction( string $order_id ): bool {

		$mailer = $this->mailer_service;

		// Get transaction.
		/** @var TransactionEntity $transaction */
		$transaction = $this->mapper_service
			->get_repository( TransactionEntity::class )
			->get_one_by( [ 'order_id' => $order_id ] );

		//  Get donor.
		/** @var DonorEntity $donor */
		$donor = $this->mapper_service
			->get_repository( DonorEntity::class )
			->get_one_by( [ 'customer_id' => $transaction->customer_id ] );

		if ( $donor->email ) {
			// Send email - email setting is checked in mailer.
			$mailer->send_receipt( $transaction );
		}

		return true;

	}

	/**
	 * Creates a payment with Mollie.
	 *
	 * @param WP_REST_Request $request
	 *
	 */
	public function submit_payment( WP_REST_Request $request ) {

		// Verify nonce.
		if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
			wp_send_json_error( [
				'message' => __( 'Request invalid.', 'kudos-donations' ),
				'nonce'   => $request->get_header( 'X-WP-Nonce' ),
			] );
		}

		$values = $request->get_body_params();

		// Check if bot filling tabs.
		if ( $this->is_bot( $values ) ) {
			wp_send_json_error( [ 'message' => __( 'Request invalid.', 'kudos-donations' ) ] );
		}

		// Add submit action and pass tabs data.
		do_action( 'kudos_submit_payment', $values );

		// Assign tabs fields.
		$value             = $values['value'];
		$payment_frequency = $values['recurring'] === 'true' ? $values['recurring_frequency'] : 'oneoff';
		$recurring_length  = $values['recurring_length'] ?? 0;
		$name              = $values['name'] ?? null;
		$business_name     = $values['business_name'] ?? null;
		$email             = $values['email_address'] ?? null;
		$street            = $values['street'] ?? null;
		$postcode          = $values['postcode'] ?? null;
		$city              = $values['city'] ?? null;
		$country           = $values['country'] ?? null;
		$message           = $values['message'] ?? null;
		$redirect_url      = $values['return_url'] ?? get_site_url();
		$campaign_id       = $values['campaign_id'] ?? null;

		$mapper = $this->mapper_service;

		if ( $email ) {

			// Search for existing donor based on email and mode.
			/** @var DonorEntity $donor */
			$donor = $mapper->get_repository( DonorEntity::class )
			                ->get_one_by( [
				                'email' => $email,
				                'mode'  => $this->vendor->get_api_mode(),
			                ] );

			// Create new donor if none found.
			if ( empty( $donor->customer_id ) ) {
				$donor    = new DonorEntity();
				$customer = $this->vendor->create_customer( $email, $name );
				$donor->set_fields( [ 'customer_id' => $customer->id ] );
			}

			// Update donor.
			$donor->set_fields(
				[
					'email'         => $email,
					'name'          => $name,
					'business_name' => $business_name,
					'mode'          => $this->vendor->get_api_mode(),
					'street'        => $street,
					'postcode'      => $postcode,
					'city'          => $city,
					'country'       => $country,
				]
			);

			$mapper->save( $donor );
		}

		$customer_id = $donor->customer_id ?? null;

		$result = $this->create_payment(
			$value,
			$payment_frequency,
			$recurring_length,
			$redirect_url,
			$campaign_id,
			$name,
			$email,
			$customer_id,
			$message
		);

		// Return checkout url if payment successfully created in Mollie.
		if ( $result instanceof Payment ) {
			do_action( 'kudos_payment_submit_successful', $values );
			wp_send_json_success( $result->getCheckoutUrl() );
		}

		// If payment not created return an error message.
		wp_send_json_error( [
			'message' => __( 'Error creating Mollie payment. Please try again later.', 'kudos-donations' ),
		] );

	}

	/**
	 * Checks the provided honeypot field and logs request if bot detected.
	 *
	 * @param $values
	 *
	 * @return bool
	 */
	public function is_bot( $values ): bool {

		$timeDiff = abs( $values['timestamp'] - time() );

		// Check if tabs completed too quickly.
		if ( $timeDiff < 4 ) {
			$this->logger->info( 'Bot detected, rejecting tabs.', [
				'reason'     => 'FormTab completed too quickly',
				'time_taken' => $timeDiff,
			] );

			return true;
		}

		// Check if honeypot field completed.
		if ( ! empty( $values['donation'] ) ) {
			$this->logger->info( 'Bot detected, rejecting tabs.',
				array_merge( [
					'reason' => 'Honeypot field completed',
				], $values ) );

			return true;
		}

		return false;
	}

	/**
	 * Creates a payment and returns it as an object.
	 *
	 * @param string $value Value of payment.
	 * @param string $interval Interval of payment (oneoff, first, recurring).
	 * @param string $years Number of years for subscription.
	 * @param string $redirect_url URL to redirect customer to on payment completion.
	 * @param string|null $campaign_id Campaign name to associate payment to.
	 * @param string|null $name Name of donor.
	 * @param string|null $email Email of donor.
	 * @param string|null $customer_id Mollie customer id.
	 * @param string|null $message Message left by donor.
	 *
	 * @return false|Object
	 */
	public function create_payment(
		string $value,
		string $interval,
		string $years,
		string $redirect_url,
		string $campaign_id = null,
		string $name = null,
		string $email = null,
		string $customer_id = null,
		string $message = null
	) {

		$order_id        = Utils::generate_id( 'kdo_' );
		$currency        = 'EUR';
		$formatted_value = number_format( $value, 2, '.', '' );

		// Set payment frequency.
		$frequency_text = Utils::get_frequency_name( $interval );
		$sequence_type  = 'oneoff' === $interval ? 'oneoff' : 'first';

		// Create payment settings.
		$payment_array = [
			"amount"       => [
				'currency' => $currency,
				'value'    => $formatted_value,
			],
			'redirectUrl'  => $redirect_url,
			'webhookUrl'   => $this->vendor->get_webhook_url(),
			'sequenceType' => $sequence_type,
			'description'  => sprintf(
			/* translators: %s: The order id */
				__( 'Kudos Donation (%1$s) - %2$s', 'kudos-donations' ),
				$frequency_text,
				$order_id
			),
			'metadata'     => [
				'order_id'    => $order_id,
				'interval'    => $interval,
				'years'       => $years,
				'email'       => $email,
				'name'        => $name,
				'campaign_id' => $campaign_id,
			],
		];

		// Link payment to customer if specified.
		if ( $customer_id ) {
			$payment_array['customerId'] = $customer_id;
		}

		$payment = $this->vendor->create_payment( $payment_array );
		if ( null === $payment ) {
			return false;
		}

		$transaction = new TransactionEntity(
			[
				'order_id'      => $order_id,
				'customer_id'   => $customer_id,
				'value'         => $formatted_value,
				'currency'      => $currency,
				'status'        => $payment->status,
				'mode'          => $payment->mode,
				'sequence_type' => $payment->sequenceType,
				'campaign_id'   => $campaign_id,
				'message'       => $message,
			]
		);

		// Add order id query arg to return url if option to show message enabled.
		if ( get_option( '_kudos_completed_payment' ) === 'message' ) {
			$action               = 'order_complete';
			$redirect_url         = add_query_arg(
				[
					'kudos_action'   => 'order_complete',
					'kudos_order_id' => $order_id,
					'kudos_nonce'    => wp_create_nonce( $action . $order_id ),
				],
				$redirect_url
			);
			$payment->redirectUrl = $redirect_url;
			$payment->update();
		}

		// Commit transaction to database.
		$mapper = $this->mapper_service;
		$mapper->save( $transaction );

		$this->logger->info(
			"New $this->vendor payment created.",
			[ 'oder_id' => $order_id, 'sequence_type' => $payment->sequenceType ]
		);

		return $payment;

	}

	/**
	 * Cancel the specified subscription.
	 *
	 * @param string $id subscription row ID.
	 *
	 * @return bool
	 */
	public function cancel_subscription( string $id ): bool {

		$mapper = $this->mapper_service;

		// Get subscription entity from supplied row id.
		/** @var SubscriptionEntity $subscription */
		$subscription = $mapper->get_repository( SubscriptionEntity::class )
		                       ->get_one_by( [ 'id' => $id ] );

		// Cancel subscription with vendor.
		$result = $subscription && $this->vendor->cancel_subscription( $subscription );

		if ( $result ) {

			// Update entity with canceled status.
			$subscription->set_fields( [
				'status' => 'cancelled',
			] );

			// Save changes to subscription entity.
			$mapper->save( $subscription );

			$this->logger->info(
				'Subscription cancelled.',
				[
					'id'              => $subscription->id,
					'subscription_id' => $subscription->subscription_id,
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
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function handle_webhook( WP_REST_Request $request ) {

		return $this->vendor->rest_webhook( $request );

	}
}
