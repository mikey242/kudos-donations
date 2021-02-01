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
use WP_REST_Server;

class PaymentService extends AbstractService {

	/**
	 * Namespace used for registering the routes
	 */
	const REST_NAMESPACE = 'kudos/v1';

	/**
	 * The route used for payment webhook
	 */
	const WEBHOOK_ROUTE = '/payment/webhook';

	/**
	 * New payment route
	 */
	const PAYMENT_ROUTE = '/payment/create';

	/**
	 * Rest route used for checking if api key is valid
	 */
	const TEST_API = '/check-api';

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

		switch ( Settings::get_setting( 'payment_vendor' ) ) {
			case 'mollie':
				$this->vendor = MollieVendor::factory();
				break;
			default:
				$this->vendor = MollieVendor::factory();
				$this->logger->critical( 'No payment vendor specified. Using Mollie.' );
		}

	}

	/**
	 * Register the vendor's rest routes
	 *
	 * @since 2.3.0
	 */
	public function register_rest_routes() {

		$routes = [
			self::PAYMENT_ROUTE => [
				'methods'             => 'POST',
				'callback'            => [ $this, 'submit_payment' ],
				'permission_callback' => '__return_true',
			],

			self::WEBHOOK_ROUTE => [
				'methods'             => 'POST',
				'callback'            => [ $this, 'handle_webhook' ],
				'args'                => [
					'id' => [
						'required' => true,
					],
				],
				'permission_callback' => '__return_true',
			],

			self::TEST_API => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'check_api_keys' ],
				'args'                => [
					'apiMode' => [
						'required' => true,
					],
				],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			],
		];

		foreach ( $routes as $key => $route ) {
			register_rest_route( self::REST_NAMESPACE, $key, $route );
		}
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

		// Verify nonce
		if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
			wp_send_json_error( [
				'message' => __( 'Request invalid.', 'kudos-donations' ),
				'nonce'   => $request->get_header( 'X-WP-Nonce' ),
			] );
		}

		// Sanitize form fields.
		$value             = intval( $request['value'] );
		$payment_frequency = isset( $request['recurring_frequency'] ) ? sanitize_text_field( $request['recurring_frequency'] ) : 'oneoff';
		$recurring_length  = isset( $request['recurring_length'] ) ? intval( $request['recurring_length'] ) : 0;
		$name              = isset( $request['name'] ) ? sanitize_text_field( $request['name'] ) : null;
		$email             = isset( $request['email_address'] ) ? sanitize_email( $request['email_address'] ) : null;
		$street            = isset( $request['street'] ) ? sanitize_text_field( $request['street'] ) : null;
		$postcode          = isset( $request['postcode'] ) ? sanitize_text_field( $request['postcode'] ) : null;
		$city              = isset( $request['city'] ) ? sanitize_text_field( $request['city'] ) : null;
		$country           = isset( $request['country'] ) ? sanitize_text_field( $request['country'] ) : null;
		$redirect_url      = isset( $request['return_url'] ) ? sanitize_text_field( $request['return_url'] ) : null;
		$campaign_id       = isset( $request['campaign_id'] ) ? sanitize_text_field( $request['campaign_id'] ) : null;

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

			// Update new/existing donor.
			$donor->set_fields(
				[
					'email'    => $email,
					'name'     => $name,
					'mode'     => $this->vendor->get_api_mode(),
					'street'   => $street,
					'postcode' => $postcode,
					'city'     => $city,
					'country'  => $country,
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
			$customer_id
		);

		// Return checkout url if payment successfully created in Mollie
		if ( $result instanceof Payment ) {
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
	 * @param string $subscription_id Mollie subscription id.
	 * @param null|string $customer_id Mollie customer id.
	 *
	 * @return bool
	 * @since   2.3.0
	 */
	public function cancel_subscription( string $subscription_id, $customer_id = null ): bool {

		$mapper = new MapperService( SubscriptionEntity::class );

		/** @var SubscriptionEntity $subscription */
		$subscription = $mapper->get_one_by( [ 'subscription_id' => $subscription_id ] );

		if ( $subscription ) {

			$this->vendor->cancel_subscription( $subscription_id, $customer_id );

			$this->logger->info(
				'Subscription cancelled.',
				[ 'customer_id' => $customer_id, 'subscription_id' => $subscription_id ]
			);

			$subscription->set_fields(
				[
					'status' => 'cancelled',
				]
			);

			return $mapper->save( $subscription ) > 1;
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
	 *
	 * @return false|object
	 * @since      2.3.0
	 */
	public function create_payment(
		string $value,
		string $interval,
		string $years,
		string $redirect_url,
		string $campaign_id = null,
		string $name = null,
		string $email = null,
		string $customer_id = null
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
			'webhookUrl'   => $_ENV['WEBHOOK_URL'] ?? rest_url( self::REST_NAMESPACE . self::WEBHOOK_ROUTE ),
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
		if(null === $payment) {
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
			]
		);

		// Commit transaction to database
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
	 * Check the vendor api key key associated with the mode
	 *
	 * @param WP_REST_Request $request Request array.
	 *
	 * @since    2.3.0
	 */
	public function check_api_keys( WP_REST_Request $request ) {

		Settings::update_setting( 'mollie_connected', 0 );

		$mode    = sanitize_text_field( $request['apiMode'] );
		$api_key = sanitize_text_field( $request[ $mode . 'Key' ] );

		// Check that the api key corresponds to the mode.
		if ( substr( $api_key, 0, 4 ) !== $mode ) {
			wp_send_json_error(
			/* translators: %s: API mode */
				sprintf( __( '%1$s API key should begin with %2$s', 'kudos-donations' ), ucfirst( $mode ), $mode . '_' )
			);
		}

		// Test the api key.
		$result = $this->vendor->test_api_connection( $api_key );

		if ( $result ) {
			Settings::update_setting( 'mollie_' . $mode . '_api_key', $api_key );
			Settings::update_setting( 'mollie_api_mode', $mode );
			Settings::update_setting( 'mollie_connected', 1 );
			wp_send_json_success(
			/* translators: %s: API mode */
				sprintf( __( '%s API key connection was successful!', 'kudos-donations' ), ucfirst( $mode ) )
			);
		} else {
			wp_send_json_error(
			/* translators: %s: API mode */
				sprintf( __( 'Error connecting with Mollie, please check the %s API key and try again.',
					'kudos-donations' ),
					ucfirst( $mode ) )
			);
		}
	}

	/**
	 * Webhook handler
	 *
	 * @param WP_REST_Request $request Request array.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @since    2.3.0
	 */
	public function handle_webhook( WP_REST_Request $request ) {

		return $this->vendor->rest_webhook( $request );

	}
}
