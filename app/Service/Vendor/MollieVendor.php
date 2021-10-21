<?php

namespace Kudos\Service\Vendor;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
use Kudos\Service\LoggerService;
use Kudos\Service\MapperService;
use Kudos\Service\RestRouteService;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\BaseCollection;
use Mollie\Api\Resources\Customer;
use Mollie\Api\Resources\MethodCollection;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Subscription;
use Mollie\Api\Resources\SubscriptionCollection;
use Mollie\Api\Types\SequenceType;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class MollieVendor implements VendorInterface {

	/**
	 * This is the name of the vendor as displayed to the user.
	 */
	const VENDOR_NAME = 'Mollie';

	/**
	 * Instance of MollieApiClient
	 *
	 * @var MollieApiClient
	 */
	private $api_client;
	/**
	 * The API mode (test or live)
	 *
	 * @var string
	 */
	private $api_mode;
	/**
	 * @var \Kudos\Service\LoggerService
	 */
	private $logger;
	/**
	 * @var \Kudos\Service\MapperService
	 */
	private $mapper;
	/**
	 * @var array
	 */
	private $api_keys;

	/**
	 * Mollie constructor.
	 */
	public function __construct( MapperService $mapper_service, LoggerService $logger_service ) {

		$this->logger = $logger_service;
		$this->mapper = $mapper_service;

		$settings = Settings::get_setting( 'vendor_mollie' );

		$this->api_client = new MollieApiClient();
		$this->api_keys   = [
			'test' => $settings['test_key'] ?? '',
			'live' => $settings['live_key'] ?? '',
		];

		$this->set_api_mode( $settings['mode'] );
	}

	/**
	 * Change the API client to the key for the specified mode.
	 */
	private function set_api_mode( ?string $mode ) {

		$key = $this->api_keys[ $mode ] ?? false;

		if ( $key ) {
			try {
				$this->api_client->setApiKey( $key );
				$this->api_mode = $mode;
			} catch ( ApiException $e ) {
				$this->logger->critical( $e->getMessage() );
			}

		}
	}

	public static function get_vendor_name(): string {
		return static::VENDOR_NAME;
	}

	/**
	 * Check the Mollie api keys for both test and live keys. Sends a JSON response.
	 */
	public function check_api_keys() {

		Settings::update_array( 'vendor_mollie',
			[
				'connected' => false,
				'recurring' => false,
			] );

		$modes    = [ "test", "live" ];
		$api_keys = $this->api_keys;

		// Check that the api key corresponds to each mode.
		foreach ( $modes as $mode ) {
			$api_key = $api_keys[ $mode ];
			if ( substr( $api_key, 0, 5 ) !== $mode . "_" ) {
				wp_send_json_error(
					[
						/* translators: %s: API mode */
						'message' => sprintf( __( '%1$s API key should begin with %2$s', 'kudos-donations' ),
							ucfirst( $mode ),
							$mode . '_' ),
						'setting' => Settings::get_setting( 'vendor_mollie' ),
					]
				);
			}

			// Test the api key.
			if ( ! $this->refresh_api_connection( $api_key ) ) {
				wp_send_json_error(
					[
						/* translators: %s: API mode */
						'message' => sprintf( __( 'Error connecting with Mollie, please check the %s API key and try again.',
							'kudos-donations' ),
							ucfirst( $mode ) ),
						'setting' => Settings::get_setting( 'vendor_mollie' ),
					]
				);
			}
		}
		// Update vendor settings.
		Settings::update_array( 'vendor_mollie',
			[
				'recurring'       => $this->can_use_recurring(),
				'connected'       => true,
				'payment_methods' => array_map( function ( $method ) {
					return [
						'id'            => $method->id,
						'status'        => $method->status,
						'maximumAmount' => (array) $method->maximumAmount,
					];
				},
					(array) $this->get_payment_methods() ),
			] );

		wp_send_json_success(
			[
				'message' =>
				/* translators: %s: API mode */
					__( 'API connection was successful!', 'kudos-donations' ),
				'setting' => Settings::get_setting( 'vendor_mollie' ),
			]
		);
	}

	/**
	 * Returns all subscriptions for customer
	 *
	 * @param string $customer_id Mollie customer id.
	 *
	 * @return SubscriptionCollection|false
	 */
	public function get_subscriptions( string $customer_id ) {

		$mollie_api = $this->api_client;

		try {
			$customer = $mollie_api->customers->get( $customer_id );

			return $customer->subscriptions();
		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage() );

			return false;
		}

	}

	/**
	 * Cancel the specified subscription
	 *
	 * @param SubscriptionEntity $subscription Instance of SubscriptionEntity.
	 *
	 * @return bool
	 */
	public function cancel_subscription( SubscriptionEntity $subscription ): bool {

		$customer_id     = $subscription->customer_id;
		$subscription_id = $subscription->subscription_id;

		$customer = $this->get_customer( $customer_id );

		// Bail if no subscription found locally or if not active.
		if ( 'active' !== $subscription->status || null === $customer ) {
			return false;
		}

		// Cancel the subscription via Mollie's API.
		try {
			$response = $customer->cancelSubscription( $subscription_id );

			/** @var Subscription $response */
			return ( $response->status === 'canceled' );
		} catch ( ApiException $e ) {
			$this->logger->error( $e->getMessage() );

			return false;
		}
	}

	/**
	 * Checks the provided api key by attempting to get associated payments
	 *
	 * @param string $api_key API key to test.
	 *
	 * @return bool
	 */
	public function refresh_api_connection( string $api_key ): bool {

		if ( ! $api_key ) {
			return false;
		}

		try {
			// Perform test call to verify api key.
			$mollie_api = $this->api_client;
			$mollie_api->setApiKey( $api_key );
			$mollie_api->payments->page();
		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage() );

			return false;
		}

		return true;

	}

	/**
	 * Gets specified payment
	 *
	 * @param string $mollie_payment_id Mollie payment id.
	 *
	 * @return bool|Payment
	 */
	public function get_payment( string $mollie_payment_id ) {

		$mollie_api = $this->api_client;

		try {
			return $mollie_api->payments->get( $mollie_payment_id );
		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage() );
		}

		return false;

	}

	/**
	 * Create a Mollie customer.
	 *
	 * @param string $email Donor email address.
	 * @param string $name Donor name.
	 *
	 * @return bool|Customer
	 */
	public function create_customer( string $email, string $name ) {

		$customer_array = [
			'email' => $email,
		];

		if ( $name ) {
			$customer_array['name'] = $name;
		}

		try {
			return $this->api_client->customers->create( $customer_array );
		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage() );

			return false;
		}
	}

	/**
	 * Get the customer from Mollie
	 *
	 * @param $customer_id
	 *
	 * @return Customer|null
	 */
	public function get_customer( $customer_id ): ?Customer {

		try {
			return $this->api_client->customers->get( $customer_id );
		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage() );

			return null;
		}

	}

	/**
	 * Creates a payment and returns it as an object
	 *
	 * @param array $payment_array Parameters to pass to mollie to create a payment.
	 *
	 * @return null|Payment
	 */
	public function create_payment( array $payment_array ): ?Payment {

		try {
			return $this->api_client->payments->create( $payment_array );
		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage() );

			return null;
		}

	}

	/**
	 * Creates a subscription based on the provided TransactionEntity
	 *
	 * @param TransactionEntity $transaction
	 * @param string $mandate_id
	 * @param string $interval
	 * @param string $years
	 *
	 * @return false|Subscription
	 */
	public function create_subscription(
		TransactionEntity $transaction,
		string $mandate_id,
		string $interval,
		string $years
	) {

		$customer_id = $transaction->customer_id;
		$start_date  = gmdate( 'Y-m-d', strtotime( '+' . $interval ) );
		$currency    = 'EUR';
		$value       = number_format( $transaction->value, 2 );

		$subscription_array = [
			'amount'      => [
				'value'    => $value,
				'currency' => $currency,
			],
			'webhookUrl'  => $this->get_webhook_url(),
			'mandateId'   => $mandate_id,
			'interval'    => $interval,
			'startDate'   => $start_date,
			'description' => sprintf(
			/* translators: %1$s: Subscription interval. %2$s: Order id. */
				__( 'Kudos Subscription (%1$s) - %2$s', 'kudos-donations' ),
				$interval,
				$transaction->order_id
			),
			'metadata'    => [
				'campaign_id' => $transaction->campaign_id,
			],
		];

		if ( 'test' === $transaction->mode ) {
			unset( $subscription_array['startDate'] );  // Disable for test mode.
		}

		if ( $years && $years > 0 ) {
			$subscription_array['times'] = Utils::get_times_from_years( $years, $interval );
		}

		$customer      = $this->get_customer( $customer_id );
		$valid_mandate = $this->check_mandate( $customer, $mandate_id );

		// Create subscription if valid mandate found
		if ( $valid_mandate ) {
			try {
				$subscription       = $customer->createSubscription( $subscription_array );
				$kudos_subscription = new SubscriptionEntity(
					[
						'transaction_id'  => $transaction->transaction_id,
						'customer_id'     => $customer_id,
						'frequency'       => $interval,
						'years'           => $years,
						'value'           => $value,
						'currency'        => $currency,
						'subscription_id' => $subscription->id,
						'status'          => $subscription->status,
					]
				);
				$this->mapper->save( $kudos_subscription );

				return $subscription;
			} catch ( ApiException $e ) {
				$this->logger->error( $e->getMessage(), [
					'transaction' => $transaction,
					'mandate_id'  => $mandate_id,
					'interval'    => $interval,
					'years'       => $years,
				] );

				return false;
			}
		}

		// No valid mandates
		$this->logger->error(
			'Cannot create subscription as customer has no valid mandates.',
			[ $customer_id ]
		);

		return false;
	}

	/**
	 * Check the provided customer for valid mandates
	 *
	 * @param Customer $customer
	 * @param string $mandate_id
	 *
	 * @return bool
	 */
	private function check_mandate( Customer $customer, string $mandate_id ): bool {

		try {
			$mandate = $customer->getMandate( $mandate_id );
			if ( $mandate->isValid() || $mandate->isPending() ) {
				return true;
			}
		} catch ( ApiException $e ) {
			$this->logger->error( $e->getMessage() );
		}

		return false;
	}

	/**
	 * Uses get_payment_methods to determine if account can receive recurring payments.
	 *
	 * @return bool
	 * @since 2.3.9
	 */
	public function can_use_recurring(): bool {

		$methods = $this->get_payment_methods( [
			'sequenceType' => 'recurring',
		] );

		if ( $methods ) {
			return $methods->count > 0;
		}

		return false;

	}

	/**
	 * Gets a list of payment methods for the current Mollie account
	 *
	 * @param array $options https://docs.mollie.com/reference/v2/methods-api/list-methods
	 *
	 * @return BaseCollection|MethodCollection|null
	 */
	public function get_payment_methods( array $options = [] ) {

		try {

			return $this->api_client->methods->allActive( $options );

		} catch ( ApiException $e ) {
			$this->logger->critical( $e->getMessage() );

			return null;
		}

	}

	/**
	 * Mollie webhook action
	 *
	 * @param WP_REST_Request $request Request array.
	 *
	 * @return WP_Error|WP_REST_Response
	 * @since    1.0.0
	 */
	public function rest_webhook( WP_REST_Request $request ) {

		// ID is case-sensitive (e.g: tr_HUW39xpdFN).
		$id = $request->get_param( 'id' );

		/**
		 * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
		 */
		$response = rest_ensure_response(
			[
				'success' => true,
				'id'      => $id,
			]
		);

		$response->add_link( 'self', rest_url( $request->get_route() ) );

		/**
		 * Get the payment object from Mollie.
		 *
		 * @var Payment $payment Mollie payment object.
		 */
		$payment = $this->get_payment( $id );
		$this->logger->info(
			"Webhook requested by $this.",
			[
				'transaction_id' => $id,
				'status'         => $payment->status,
				'sequence_type'  => $payment->sequenceType,
			]
		);

		/**
		 *
		 * To not leak any information to malicious third parties, it is recommended
		 * to return a 200 OK response even if the ID is not known to your system.
		 *
		 * @link https://docs.mollie.com/guides/webhooks#how-to-handle-unknown-ids
		 */
		if ( null === $payment ) {
			return $response;
		}

		//Create webhook action.
		do_action( 'kudos_mollie_webhook_requested', $payment );

		//Get required data from payment object.
		$transaction_id = $payment->id;
		$order_id       = $payment->metadata->order_id ?? Utils::generate_id( 'kdo_' );
		$amount         = $payment->amount;

		switch ( $payment->sequenceType ) {

			/**
			 * Update existing transaction.
			 * This applies to 'oneoff' and 'first' sequence types.
			 */
			case SequenceType::SEQUENCETYPE_ONEOFF:
			case SequenceType::SEQUENCETYPE_FIRST:

				/** @var TransactionEntity $transaction */
				$transaction = $this->mapper
					->get_repository( TransactionEntity::class )
					->get_one_by(
						[
							'order_id'       => $order_id,
							'transaction_id' => $transaction_id,
						],
						'OR'
					);

				// Add refund if present.
				if ( $payment->hasRefunds() ) {

					do_action( 'kudos_mollie_refund', $order_id );

					$transaction->set_fields(
						[
							'refunds' => json_encode(
								[
									'refunded'  => $payment->getAmountRefunded(),
									'remaining' => $payment->getAmountRemaining(),
								]
							),
						]
					);

					$this->logger->info( 'Payment refunded.', [ 'transaction' => $transaction ] );

				} else {
					// Check if status is the same (in case of multiple webhook calls).
					if ( $transaction->status === $payment->status ) {
						$this->logger->debug( 'Multiple webhook call detected, ignoring', [
							'transaction_id' => $id,
							'status'         => $payment->status,
							'sequence_type'  => $payment->sequenceType,
						] );

						return $response;
					}
				}

				if ( $payment->isPaid() && ! $payment->hasRefunds() && ! $payment->hasChargebacks() ) {

					// Schedule processing for later.
					do_action( 'kudos_mollie_transaction_paid', $order_id );

					// Set up recurring payment if sequence is first.
					if ( $payment->hasSequenceTypeFirst() ) {
						$this->logger->debug( 'Creating subscription', [ $transaction ] );
						$this->create_subscription(
							$transaction,
							$payment->mandateId,
							$payment->metadata->interval,
							$payment->metadata->years
						);
					}
				}

				break;

			/**
			 * Assumed SequenceType::SEQUENCETYPE_RECURRING. Create new transaction.
			 * This applies to 'recurring' sequence types.
			 */
			default:

				$transaction = new TransactionEntity( [
					'order_id' => $order_id,
				] );

				$subscription_id = $payment->subscriptionId;
				$customer_id     = $payment->customerId;
				$customer        = $this->get_customer( $customer_id );

				try {
					$subscription_meta = $customer->getSubscription( $subscription_id )->metadata;
					if ( isset( $subscription_meta->campaign_id ) ) {
						$campaign_id = $subscription_meta->campaign_id;
						$transaction->set_fields(
							[
								'campaign_id' => $campaign_id,
							]
						);
					}
				} catch ( ApiException $e ) {
					$this->logger->error( $e->getMessage() );
				}

				break;
		}

		// Update payment.
		$transaction->set_fields(
			[
				'status'          => $payment->status,
				'transaction_id'  => $transaction_id,
				'customer_id'     => $payment->customerId,
				'value'           => $amount->value,
				'currency'        => $amount->currency,
				'sequence_type'   => $payment->sequenceType,
				'method'          => $payment->method,
				'mode'            => $payment->mode,
				'subscription_id' => $payment->subscriptionId,
			]
		);

		// Save transaction to database.
		$this->mapper->save( $transaction );

		return $response;
	}

	/**
	 * Returns the api mode.
	 *
	 * @return string
	 */
	public function get_api_mode(): string {

		return $this->api_mode;

	}

	/**
	 * Returns the vendor name.
	 *
	 * @return string
	 */
	public function __toString(): string {
		return self::get_vendor_name();
	}

	/**
	 * Returns the Mollie Rest URL.
	 *
	 * @return string
	 */
	public static function get_webhook_url(): string {
		$route = RestRouteService::NAMESPACE . RestRouteService::PAYMENT_WEBHOOK;

		// Use APP_URL if defined in .env file.
		if ( isset( $_ENV['APP_URL'] ) ) {
			return $_ENV['APP_URL'] . 'wp-json/' . $route;
		}

		// Otherwise, return normal rest URL.
		return rest_url( RestRouteService::NAMESPACE . RestRouteService::PAYMENT_WEBHOOK );
	}

	/**
	 * Syncs Mollie transactions with the local DB.
	 * Returns the number of transactions updated.
	 *
	 * @return int
	 */
	public function sync_transactions(): int {
		$updated = 0;
		$mapper  = $this->mapper;
		$mapper->get_repository( DonorEntity::class );
		$donors = $mapper->get_all_by();
		/** @var DonorEntity $donor */
		foreach ( $donors as $donor ) {
			$customer_id = $donor->customer_id;
			if ( $donor->mode !== $this->api_mode ) {
				$this->set_api_mode( $donor->mode );
			}
			$customer = $this->get_customer( $customer_id );
			if ( $customer ) {
				try {
					$payments = $customer->payments();
					foreach ( $payments as $payment ) {
						$amount   = $payment->amount;
						$order_id = $payment->metadata->order_id ?? null;
						$mapper->get_repository( TransactionEntity::class );

						if ( $order_id ) {

							/**
							 * Find existing transaction.
							 * @var TransactionEntity $transaction
							 */
							$transaction = $mapper->get_one_by( [
								'order_id' => $order_id,
								'status'   => 'open',
							] );

							if ( $transaction ) {
								$transaction->set_fields(
									[
										'status'          => $payment->status,
										'customer_id'     => $payment->customerId,
										'value'           => $amount->value,
										'currency'        => $amount->currency,
										'sequence_type'   => $payment->sequenceType,
										'method'          => $payment->method,
										'mode'            => $payment->mode,
										'subscription_id' => $payment->subscriptionId,
										'transaction_id'  => $payment->id,
										'campaign_id'     => $payment->metadata ? $payment->metadata->campaign_id : null,
									]
								);
								$mapper->save( $transaction );
								$updated ++;
							}
						}
					}
				} catch ( ApiException $e ) {
					$this->logger->error( $e->getMessage() );
				}
			}
		}

		return $updated;
	}

	/**
	 * Adds missing transactions from Mollie.
	 * Returns the number of transactions added.
	 *
	 * @return int
	 */
	public function add_missing_transactions(): int {
		$added  = 0;
		$mapper = $this->mapper;
		$mapper->get_repository( DonorEntity::class );
		$donors = $mapper->get_all_by();
		/** @var DonorEntity $donor */
		foreach ( $donors as $donor ) {
			$customer_id = $donor->customer_id;
			if ( $donor->mode !== $this->api_mode ) {
				$this->set_api_mode( $donor->mode );
			}
			$customer = $this->get_customer( $customer_id );
			if ( $customer ) {
				try {
					$payments = $customer->payments();
					foreach ( $payments as $payment ) {
						$order_id = $payment->metadata->order_id ?? null;

						if ( $order_id ) {

							$mapper->get_repository( TransactionEntity::class );

							/**
							 * Find existing transaction.
							 * @var TransactionEntity $transaction
							 */
							$transaction = $mapper->get_one_by( [
								'order_id' => $order_id,
							] );

							// Add new transaction if none found.
							if ( ! $transaction ) {

								$transaction = new TransactionEntity( [
									'order_id'        => $order_id,
									'created'         => $payment->createdAt,
									'status'          => $payment->status,
									'customer_id'     => $payment->customerId,
									'value'           => $payment->amount->value,
									'currency'        => $payment->amount->currency,
									'sequence_type'   => $payment->sequenceType,
									'method'          => $payment->method,
									'mode'            => $payment->mode,
									'subscription_id' => $payment->subscriptionId,
									'transaction_id'  => $payment->id,
									'campaign_id'     => $payment->metadata ? $payment->metadata->campaign_id : null,
								] );

								$mapper->save( $transaction );
								$added ++;
							}

						}
					}
				} catch ( ApiException $e ) {
					$this->logger->error( $e->getMessage() );
				}
			}
		}

		return $added;
	}
}
