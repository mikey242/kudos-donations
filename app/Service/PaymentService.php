<?php

namespace Kudos\Service;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\Vendor\AbstractVendor;
use Kudos\Service\Vendor\MollieVendor;
use Mollie\Api\Resources\Payment;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class PaymentService extends AbstractService {

	/**
	 * @var AbstractVendor
	 */
	private $vendor;

	/**
	 * Payment service constructor.
	 *
	 * @since      2.3.0
	 */
	public function __construct() {

		parent::__construct();

		$vendor       = $this::get_current_vendor_class();
		$this->vendor = call_user_func( [ $vendor, 'factory' ] );

	}

	/**
	 * Returns current vendor class.
	 *
	 * @return AbstractVendor
	 */
	private static function get_current_vendor_class(): string {
		switch ( Settings::get_setting( 'payment_vendor' ) ) {
			case 'mollie':
				return MollieVendor::class;
			default:
				$logger = new LoggerService();
				$logger->warning( 'No payment vendor specified. Using Mollie.' );

				return MollieVendor::class;
		}
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
	 * Checks if required api settings are saved before displaying button
	 *
	 * @return bool
	 * @since   2.4.6
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
	 * Processes the transaction. Used by action scheduler.
	 *
	 * @param string $order_id Kudos order id.
	 *
	 * @return bool
	 * @since   2.3.0
	 */
	public static function process_transaction( string $order_id ): bool {

		// Bail if no order ID.
		if ( null === $order_id ) {
			return false;
		}

		$mapper = new MapperService( TransactionEntity::class );
		/** @var TransactionEntity $transaction */
		$transaction = $mapper->get_one_by( [ 'order_id' => $order_id ] );

		if ( $transaction->get_donor()->email ) {
			// Send email - email setting is checked in mailer.
			$mailer = MailerService::factory();
			$mailer->send_receipt( $transaction );
		}

		return true;

	}

	/**
	 * Creates a payment with Mollie.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @since   2.3.0
	 */
	public function submit_payment( WP_REST_Request $request ) {

		// Verify nonce.
		if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
			wp_send_json_error( [
				'message' => __( 'Request invalid.', 'kudos-donations' ),
				'nonce'   => $request->get_header( 'X-WP-Nonce' ),
			] );
		}

		$values = $request->get_json_params();

		// Add submit action and pass form data.
		do_action( 'kudos_submit_payment', $values );

		// Assign form fields.
		$value             = $values['value'];
		$payment_frequency = $values['recurring_frequency'] ?? 'oneoff';
		$recurring_length  = $values['recurring_length'] ?? 0;
		$name              = $values['name'] ?? null;
		$business_name     = $values['business_name'] ?? null;
		$email             = $values['email_address'] ?? null;
		$street            = $values['street'] ?? null;
		$postcode          = $values['postcode'] ?? null;
		$city              = $values['city'] ?? null;
		$country           = $values['country'] ?? null;
		$message           = $values['message'] ?? null;
		$redirect_url      = $values['return_url'] ?? null;
		$campaign_id       = $values['campaign_id'] ?? null;

		$mapper = new MapperService( DonorEntity::class );

		if ( $email ) {

			// Search for existing donor based on email and mode.
			/** @var DonorEntity $donor */
			$donor = $mapper->get_one_by( [
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

		do_action( 'kudos_payment_submit_successful', $values );

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

		// Return checkout url if payment successfully created in Mollie
		if ( $result instanceof Payment ) {
			do_action( 'kudos_payment_submit_successful', $values );
			wp_send_json_success( $result->getCheckoutUrl() );
		}

		// If payment not created return an error message
		wp_send_json_error( [
			'message' => __( 'Error creating Mollie payment. Please try again later.', 'kudos-donations' ),
		] );

	}

	/**
	 * Cancel the specified subscription
	 *
	 * @param string $id subscription row ID.
	 *
	 * @return bool
	 * @since   2.3.0
	 */
	public function cancel_subscription( string $id ): bool {

		$mapper = new MapperService( SubscriptionEntity::class );

		// Get subscription entity from supplied row id.
		/** @var SubscriptionEntity $subscription */
		$subscription = $mapper->get_one_by( [ 'id' => $id ] );

		// Cancel subscription with vendor.
		$result = $subscription ?? $this->vendor->cancel_subscription( $subscription );

		if ( $result ) {

			// Update entity with canceled status.
			$subscription->set_fields( [
				'status' => 'cancelled',
			] );

			// Save changes to subscription entity.
			$mapper->save( $subscription );

			$this->logger->info(
				'Subscription cancelled.',
				[ 'id' => $subscription->id ]
			);

			return true;
		}

		return false;

	}

	/**
	 * Creates a payment and returns it as an object
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
	 * @return false|Payment
	 * @since      2.3.0
	 */
	public
	function create_payment(
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

		$order_id = Utils::generate_id( 'kdo_' );
		$currency = 'EUR';
		$value    = number_format( $value, 2, '.', '' );

		// Set payment frequency.
		$frequency_text = Utils::get_frequency_name( $interval );
		$sequence_type  = 'oneoff' === $interval ? 'oneoff' : 'first';

		// Create payment settings.
		$payment_array = [
			"amount"       => [
				'currency' => $currency,
				'value'    => $value,
			],
			'redirectUrl'  => $redirect_url,
			'webhookUrl'   => $_ENV['WEBHOOK_URL'] ?? rest_url( RestRouteService::NAMESPACE . RestRouteService::PAYMENT_WEBHOOK ),
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
				'value'         => $value,
				'currency'      => $currency,
				'status'        => $payment->status,
				'mode'          => $payment->mode,
				'sequence_type' => $payment->sequenceType,
				'campaign_id'   => $campaign_id,
				'message'       => $message,
			]
		);

		// Commit transaction to database.
		$mapper = new MapperService( TransactionEntity::class );
		$mapper->save( $transaction );

		// Add order id query arg to return url if option to show message enabled.
		if ( get_option( '_kudos_return_message_enable' ) ) {
			$redirect_url         = add_query_arg(
				[
					'kudos_action'   => 'order_complete',
					'kudos_order_id' => $order_id,
					'kudos_token'    => $transaction->create_secret(),
				],
				$redirect_url
			);
			$payment->redirectUrl = $redirect_url;
			$payment->update();
			$mapper->save( $transaction );
		}

		$this->logger->info(
			'New payment created',
			[ 'oder_id' => $order_id, 'sequence_type' => $payment->sequenceType ]
		);

		return $payment;

	}

	/**
	 * Check the vendor api key key associated with the mode.
	 *
	 * @since    2.3.0
	 */
	public
	function check_api_keys() {

		Settings::update_array( 'vendor_mollie',
			[
				'connected' => false,
				'recurring' => false,
			] );

		$current = Settings::get_current_vendor_settings();
		$mode    = $current['mode'];
		$api_key = $current[ $mode . '_key' ];

		// Check that the api key corresponds to the mode.
		if ( substr( $api_key, 0, 4 ) !== $mode ) {
			wp_send_json_error(
				[
					/* translators: %s: API mode */
					'message' => sprintf( __( '%1$s API key should begin with %2$s', 'kudos-donations' ),
						ucfirst( $mode ),
						$mode . '_' ),
					'setting' => $current,
				]
			);
		}

		// Test the api key.
		$result = $this->vendor->refresh_api_connection( $api_key );

		// Update settings.
		Settings::update_array( 'vendor_mollie',
			[
				'connected' => $result,
			] );

		// Send results to JS.
		if ( $result ) {

			// Update vendor settings.
			Settings::update_array( 'vendor_mollie',
				[
					'recurring'       => $this->vendor->can_use_recurring(),
					'payment_methods' => array_map( function ( $method ) {
						return [
							'id'     => $method->id,
							'status' => $method->status,
						];
					},
						(array) $this->vendor->get_payment_methods() ),
				] );

			wp_send_json_success(
				[
					'message' =>
					/* translators: %s: API mode */
						sprintf( __( '%s API key connection was successful!', 'kudos-donations' ),
							ucfirst( $mode ) ),
					'setting' => Settings::get_current_vendor_settings(),
				]
			);
		}

		wp_send_json_error(
			[
				/* translators: %s: API mode */
				'message' => sprintf( __( 'Error connecting with Mollie, please check the %s API key and try again.',
					'kudos-donations' ),
					ucfirst( $mode ) ),
				'setting' => Settings::get_current_vendor_settings(),
			]
		);

	}

	/**
	 * Webhook handler. Passes request to rest_webhook method of current vendor.
	 *
	 * @param WP_REST_Request $request Request array.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @since    2.3.0
	 */
	public
	function handle_webhook(
		WP_REST_Request $request
	) {

		return $this->vendor->rest_webhook( $request );

	}
}
