<?php
/**
 * Mollie payment vendor.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Vendor\PaymentVendor;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\DonorEntity;
use IseardMedia\Kudos\Domain\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\RepositoryAwareInterface;
use IseardMedia\Kudos\Domain\Repository\RepositoryAwareTrait;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Service\PaymentService;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\SubscriptionStatus;
use IseardMedia\Kudos\Vendor\AbstractVendor;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Exceptions\ApiException;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Exceptions\RequestException;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\MollieApiClient;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Resources\BaseCollection;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Resources\Customer;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Resources\Method;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Resources\MethodCollection;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\PaymentMethod;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\PaymentMethodStatus;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\RefundStatus;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\SequenceType;
use WP_REST_Request;
use WP_REST_Response;

class MolliePaymentVendor extends AbstractVendor implements PaymentVendorInterface, RepositoryAwareInterface {

	use RepositoryAwareTrait;

	public const SETTING_PROFILE = '_kudos_vendor_mollie_profile';
	public const SETTING_API_MODE = '_kudos_vendor_mollie_api_mode';
	public const SETTING_RECURRING = '_kudos_vendor_mollie_recurring';
	public const SETTING_API_KEY_LIVE = '_kudos_vendor_mollie_api_key_live';
	public const SETTING_API_KEY_TEST = '_kudos_vendor_mollie_api_key_test';
	public const SETTING_API_KEY_ENCRYPTED_LIVE = '_kudos_vendor_mollie_api_key_encrypted_live';
	public const SETTING_API_KEY_ENCRYPTED_TEST = '_kudos_vendor_mollie_api_key_encrypted_test';
	public const SETTING_PAYMENT_METHODS = '_kudos_vendor_mollie_payment_methods';
	public MollieApiClient $api_client;

	/**
	 * Mollie constructor.
	 */
	public function __construct( MollieApiClient $api_client ) {
		$this->api_client = $api_client;
		add_action( 'kudos_mollie_handle_status_change', [ $this, 'handle_status_change' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->config_client();
		$this->set_user_agent();

		// Handle API key saving.
		add_filter( 'pre_update_option_' . self::SETTING_API_KEY_LIVE, [ $this, 'handle_key_update' ], 10, 3 );
		add_filter( 'pre_update_option_' . self::SETTING_API_KEY_TEST, [ $this, 'handle_key_update' ], 10, 3 );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name(): string {
		return 'Mollie';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'mollie';
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_vendor_ready(): bool {
		$mode    = $this->get_api_mode();
		$option  = constant( "self::SETTING_API_KEY_ENCRYPTED_" . strtoupper( $mode ) );
		$key     = $this->get_decrypted_key( $option );
		$methods = get_option( self::SETTING_PAYMENT_METHODS );
		return ! empty( $key ) && ! empty( $methods ) ?? false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function vendor_status(): array {
		$name = get_option(self::SETTING_PROFILE)['name'] ?? null;
		$ready = $this->is_vendor_ready();
		return [
			'ready' => $ready,
			'recurring' => $ready && $this->can_use_recurring(),
			'text'  => sprintf(__('%s ready'), self::get_name()) . ($name ? ' (' . $name . ')' : ''),
		];
	}

	/**
	 * Returns the api mode.
	 *
	 * @return string
	 */
	public function get_api_mode(): string {
		return get_option( self::SETTING_API_MODE, 'test' );
	}

	/**
	 * Change the API client to the key for the specified mode.
	 */
	protected function config_client(): void {
		// Gets the key associated with the specified mode.
		$mode   = $this->get_api_mode();
		$option = constant( "self::SETTING_API_KEY_ENCRYPTED_" . strtoupper( $mode ) );
		$key    = $this->get_decrypted_key( $option );

		if ( $key ) {
			try {
				$this->api_client->setApiKey( $key );
			} catch ( ApiException $e ) {
				$this->logger->critical( $e->getMessage() );
			}
		}
	}

	/**
	 * Sets the user agent for identifying requests made with this plugin.
	 * @see https://docs.mollie.com/docs/integration-partners-user-agent-strings
	 */
	private function set_user_agent(): void {
		global $wp_version;
		$this->api_client->addVersionString( "KudosDonations/" . KUDOS_VERSION );
		$this->api_client->addVersionString( "WordPress/" . $wp_version );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function recurring_enabled(): bool {
		return (bool) get_option( self::SETTING_RECURRING, false );
	}

	/**
	 * Uses get_payment_methods to determine if account can receive recurring payments.
	 */
	private function can_use_recurring(): bool {
		$methods = $this->get_active_payment_methods( [
			'sequenceType' => 'recurring',
		] );

		if ( $methods ) {
			return $methods->count() > 0;
		}

		return false;
	}

	/**
	 * Handles the saving of the test and live api keys.
	 *
	 * @param string $value The new value.
	 * @param string $old_value The previous value.
	 * @param string $option The option name.
	 *
	 * @return string
	 */
	public function handle_key_update( string $value, string $old_value, string $option ): string {
		$mode             = ( $option === self::SETTING_API_KEY_LIVE ) ? 'live' : 'test';
		$encrypted_option = constant( "self::SETTING_API_KEY_ENCRYPTED_" . strtoupper( $mode ) );
		$filter_name      = "kudos_mollie_{$mode}_key_validation";

		if ( ! $value ) {
			update_option( $encrypted_option, '' );
			update_option( self::SETTING_PAYMENT_METHODS, [] );

			return $value;
		}

		$should_skip_refresh = apply_filters( $filter_name, false );

		// Auto-set the mode to match the key being updated
		update_option( self::SETTING_API_MODE, $mode );

		$callback = ! $should_skip_refresh ? [ $this, 'refresh' ] : null;

		return $this->save_encrypted_key( $value, $encrypted_option, $callback );
	}


	/**
	 * {@inheritDoc}
	 */
	public function refresh(): bool {

		$this->config_client();
		// Rebuild Mollie settings.
		$payment_methods = array_map( function ( Method $method ) {
			return [
				'id'            => $method->id,
				'description'   => $method->description,
				'image'         => $method->image->svg,
				'minimumAmount' => $method->minimumAmount,
				'maximumAmount' => (array) $method->maximumAmount,
			];
		}, (array) $this->get_active_payment_methods() );

		$this->logger->debug( 'Mollie payment methods', $payment_methods );

		// No payment methods found, return false.
		if ( empty( $payment_methods ) ) {
			return false;
		}

		try {
			// Handle SEPA Direct Debit separately.
			$sepa = $this->api_client->methods->get( PaymentMethod::DIRECTDEBIT );
			if ( PaymentMethodStatus::ACTIVATED === $sepa->status ) {
				$payment_methods[] = [
					'id'            => $sepa->id,
					'description'   => $sepa->description,
					'image'         => $sepa->image->svg,
					'minimumAmount' => $sepa->minimumAmount,
					'maximumAmount' => $sepa->maximumAmount,
				];
			}
		} catch ( RequestException $e ) {
			$this->logger->critical( 'Direct debit payment method not found', ['message' => $e->getMessage()] );
		}

		try {
			// Get profile.
			$profile = $this->api_client->profiles->getCurrent();
			$this->logger->debug('Mollie profile fetched', [$profile]);
			// Update profile.
			update_option(self::SETTING_PROFILE, [
				'id' => $profile->id,
				'mode' => $profile->mode,
				'name' => $profile->name,
				'website' => $profile->website,
				'status' => $profile->status
			]);
		} catch (RequestException $e) {
			$this->logger->warning('Cannot get Mollie profile', ['message' => $e->getMessage()]);
		}

		$this->logger->debug( 'Mollie refreshed connection settings' );

		// Update payment methods.
		update_option(
			self::SETTING_PAYMENT_METHODS,
			$payment_methods
		);

		// Update recurring status.
		update_option( self::SETTING_RECURRING, $this->can_use_recurring() );

		// Update vendor status.
		$ready = $this->is_vendor_ready();
		$name = get_option(self::SETTING_PROFILE)['name'] ?? null;
		update_option(PaymentService::SETTING_VENDOR_STATUS, [
			'ready' => $ready,
			'recurring' => $ready && $this->can_use_recurring(),
			'text'  => sprintf(__('%s ready'), self::get_name()) . ($name ? ' (' . $name . ')' : ''),
		]);

		return true;
	}

	/**
	 * Gets a list of payment methods for the current Mollie account
	 *
	 * @param array $options https://docs.mollie.com/reference/v2/methods-api/list-methods
	 *
	 * @return BaseCollection|MethodCollection|null
	 */
	public function get_active_payment_methods( array $options = [] ) {
		try {
			return $this->api_client->methods->allEnabled( $options );
		} catch ( RequestException $e ) {
			$this->logger->critical( $e->getMessage() );

			return null;
		}
	}

	/**
	 * Cancel the specified subscription.
	 *
	 * @param SubscriptionEntity $subscription Instance of WP_Post.
	 *
	 * @return bool
	 */
	public function cancel_subscription( SubscriptionEntity $subscription ): bool {
		$transaction_repository = $this->get_repository(TransactionRepository::class);
		/** @var TransactionEntity $transaction */
		$transaction        = $transaction_repository->get( (int) $subscription->transaction_id );
		$donor              = $transaction_repository->get_donor( $transaction );
		$vendor_customer_id = $donor->vendor_customer_id;
		$customer           = $this->get_customer( $vendor_customer_id );

		// Bail if no subscription found locally or if not active.
		if ( SubscriptionStatus::ACTIVE !== $subscription->status || null === $customer ) {
			return false;
		}

		// Cancel the subscription via Mollie's API.
		try {
			$response = $customer->cancelSubscription( $subscription->vendor_subscription_id );

			return ( $response->status === PaymentStatus::CANCELED );
		} catch ( ApiException $e ) {
			$this->logger->error( 'Error cancelling subscription:', [ 'message' => $e->getMessage() ] );

			return false;
		}
	}

	/**
	 * Get the customer from Mollie.
	 *
	 * @param string $vendor_customer_id
	 *
	 * @return Customer|null
	 */
	public function get_customer( string $vendor_customer_id ): ?Customer {
		try {
			return $this->api_client->customers->get( $vendor_customer_id );
		} catch ( RequestException $e ) {
			$this->logger->critical( $e->getMessage() );

			return null;
		}
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
		$args = [
			'email' => $email,
		];

		if ( $name ) {
			$args['name'] = $name;
		}

		try {
			return $this->api_client->customers->create( $args );
		} catch ( RequestException $e ) {
			$this->logger->critical( $e->getMessage() );

			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function create_payment( array $payment_args, TransactionEntity $transaction, ?string $vendor_customer_id = null ) {

		$transaction_id = $transaction->id;

		// Set payment frequency.
		$payment_args['payment_frequency'] = "true" === $payment_args['recurring'] ? $payment_args['recurring_frequency'] : SequenceType::ONEOFF;
		$sequence_type                     = "true" === $payment_args['recurring'] ? SequenceType::FIRST : SequenceType::ONEOFF;
		$payment_args['value']             = number_format( floatval( $payment_args['value'] ), 2, '.', '' );
		$redirect_url                      = $payment_args['return_url'];

		// Add order id query arg to return url if option to show message enabled.
		/** @var CampaignEntity $campaign */
		$campaign            = $this->get_repository(CampaignRepository::class)
		                            ->get( (int) $payment_args['campaign_id'] );
		$show_return_message = $campaign->show_return_message ?? false;
		if ( ! empty( $show_return_message ) ) {
			$action       = 'order_complete';
			$redirect_url = add_query_arg(
				[
					'kudos_action'         => 'order_complete',
					'kudos_transaction_id' => $transaction_id,
					'kudos_nonce'          => wp_create_nonce( $action . $transaction_id ),
				],
				$payment_args['return_url']
			);
		}

		// Create payment settings.
		$payment_array = [
			"amount"       => [
				'currency' => $payment_args['currency'],
				'value'    => $payment_args['value'],
			],
			'redirectUrl'  => $redirect_url,
			'webhookUrl'   => $this->get_webhook_url(),
			'sequenceType' => $sequence_type,
			'description'  => $transaction->title,
			'metadata'     => [
				'transaction_id' => $transaction_id,
				'frequency'      => $payment_args['payment_frequency'],
				'years'          => $payment_args['recurring_length'],
				'email'          => $payment_args['email'],
				'name'           => $payment_args['name'],
				'campaign_id'    => $payment_args['campaign_id'],
			],
		];

		// Link payment to customer if specified.
		if ( $vendor_customer_id ) {
			$payment_array['customerId'] = $vendor_customer_id;
		}

		try {
			$payment = $this->api_client->payments->create( $payment_array );

			$this->logger->info(
				"New " . $this->get_name() . " payment created.",
				[ 'transaction_id' => $transaction_id, 'sequence_type' => $payment->sequenceType ]
			);

			// Checkout URL used to complete payment.
			$checkout_url = $payment->getCheckoutUrl();

			// Update transaction entity from payment object.
			$transaction->checkout_url = $checkout_url;
			$transaction->vendor_payment_id = $payment->id;
			$this->get_repository(TransactionRepository::class)
			     ->update($transaction);

			return $checkout_url;
		} catch ( RequestException $e ) {
			$this->logger->error( 'Error creating payment with Mollie', [ 'error' => $e->getMessage() ] );

			return false;
		}
	}

	/**
	 * Creates a subscription based on the provided transaction
	 *
	 * @return int|false
	 */
	public function create_subscription( TransactionEntity $transaction, string $mandate_id, string $interval, int $years	) {
		$this->logger->debug( 'Creating subscription', [
			'mandate_id' => $mandate_id,
			'interval'   => $interval,
			'years'      => $years
		] );
		/** @var DonorEntity $donor */
		$donor       = $this->get_repository(DonorRepository::class)->get( $transaction->donor_id );
		$customer_id = $donor->vendor_customer_id;
		$start_date  = gmdate( 'Y-m-d', strtotime( '+' . $interval ) );
		$currency    = $transaction->currency;
		$value       = Utils::format_value_for_use( $transaction->value );
		$customer    = $this->get_customer( $customer_id );

		// Create subscription if valid mandate found.
		if ( $this->check_mandate( $customer, $mandate_id ) ) {
			$this->logger->debug( 'Customer has valid mandate, continuing.', [ 'mandate_id' => $mandate_id ] );
			try {

				$subscriptions = $this->get_repository(SubscriptionRepository::class);

				// Create subscription entity.
				$subscription_entity = new SubscriptionEntity( [
					'frequency'      => $interval,
					'years'          => $years,
					'value'          => $value,
					'currency'       => $currency,
					'transaction_id' => $transaction->id,
					'donor_id'       => $transaction->donor_id,
					'campaign_id'    => $transaction->campaign_id
				] );
				$subscription_id     = $subscriptions->insert($subscription_entity);

				// Prepare arguments to send to Mollie.
				$subscription_args = [
					'amount'      => [
						'value'    => $value,
						'currency' => $currency,
					],
					'webhookUrl'  => $this->get_webhook_url(),
					'mandateId'   => $mandate_id,
					'interval'    => $interval,
					'startDate'   => $start_date,
					'description' => $subscription_entity->title,
					'metadata'    => [
						'campaign_id'     => $transaction->campaign_id,
						'donor_id'        => $transaction->donor_id,
						'subscription_id' => $subscription_id
					],
				];

				// Disable startDate for test mode.
				if ( 'test' === $transaction->mode ) {
					unset( $subscription_args['startDate'] );
				}

				if ( $years && $years > 0 ) {
					$subscription_args['times'] = Utils::get_times_from_years( $years, $interval );
				}

				$subscription = $customer->createSubscription( $subscription_args );
				$this->logger->debug( 'Subscription created with Mollie', [ 'result' => $subscription ] );

				// Update subscription post with status and subscription id.
				return $subscriptions->patch($subscription_id, [
					'status' => $subscription->status,
					'vendor_customer_id' => $subscription->customerId,
					'vendor_subscription_id' => $subscription->id,
				]);
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

		// No valid mandates.
		$this->logger->error(
			__( 'Cannot create subscription as customer has no valid mandates.', 'kudos-donations' ),
			[ $customer_id ]
		);

		return false;
	}

	/**
	 * Returns the Mollie Rest URL.
	 *
	 * @return string
	 */
	public static function get_webhook_url(): string {
		$route = "kudos/v1/payment/webhook";

		// Otherwise, return normal rest URL.
		return get_rest_url( null, $route );
	}

	/**
	 * Mollie webhook handler.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function rest_webhook( WP_REST_Request $request ): WP_REST_Response {
		// Sanitize request params.
		$request->sanitize_params();

		// ID is case-sensitive (e.g: tr_HUW39xpdFN).
		$payment_id = $request->get_param( 'id' );
		// Log request.
		$this->logger->info(
			"Webhook requested by " . $this::get_name(),
			[
				'payment_id' => $payment_id,
			]
		);

		/**
		 * Create success response object.
		 *
		 * @link https://developer.wordpress.org/reference/classes/wp_rest_response/
		 */
		$response = new WP_REST_Response( [
			'success' => true,
			'id'      => $payment_id,
		] );

		$response->add_link( 'self', rest_url( $request->get_route() ) );

		// Process the payment asynchronously.
		Utils::enqueue_async_action(
			'kudos_mollie_handle_status_change',
			[ 'payment_id' => $payment_id ],
			'kudos-donations'
		);

		return $response;
	}

	/**
	 * Mollie webhook handler.
	 *
	 * @param string $vendor_payment_id The Mollie payment id.
	 *
	 * @throws RequestException
	 */
	public function handle_status_change( string $vendor_payment_id ): bool {

		// Mollie API.
		$mollie = $this->api_client;

		/**
		 * Get the payment object from Mollie.
		 *
		 * @link https://docs.mollie.com/reference/v2/payments-api/get-payment
		 */
		$payment = $mollie->payments->get( $vendor_payment_id );

		// Log payment retrieval.
		$this->logger->debug(
			"Payment retrieved from Mollie.",
			[
				'vendor_id'     => $vendor_payment_id,
				'status'        => $payment->status,
				'sequence_type' => $payment->sequenceType,
				'has_refunds'   => $payment->hasRefunds(),
				'metadata'      => $payment->metadata
			]
		);

		// Get the transactions' repository.
		$transactions = $this->get_repository(TransactionRepository::class);

		/**
		 * Create new transaction if this is a recurring payment.
		 * e.g. New recurring payment.
		 */
		if ( $payment->hasSequenceTypeRecurring() ) {
			$this->logger->debug( 'Recurring payment received, creating transaction.', [
				'subscription_id' => $payment->subscriptionId,
			] );
			$customer     = $mollie->customers->get( $payment->customerId );
			$subscription = $customer->getSubscription( $payment->subscriptionId );

			//	Get post id if $campaign_id is slug from pre 4.0.0 version.
			$campaign_id         = $subscription->metadata->campaign_id;
			$campaigns = $this->get_repository( CampaignRepository::class );
			/** @var CampaignEntity $campaign */
			$campaign    = $campaigns
	                        ->find_one_by( [ 'id' => $campaign_id ] ) ??
			               $campaigns
	                        ->find_one_by( [ 'wp_post_slug' => $campaign_id ] ) ?? null;
			$campaign_id = $campaign->id;

			// Subscription id.
			$subscription_id = $subscription->metadata->subscription_id;
			if ( ! $subscription_id ) {
				/** @var SubscriptionEntity $subscription_entity */
				$subscription_entity = $this->get_repository( SubscriptionRepository::class )->find_one_by( [
					'vendor_subscription_id' => $subscription->id
				] );
				$subscription_id     = $subscription_entity->id ?? null;
			}

			// Get Donor ID. If subscription from pre 4.0.0, use customerId to get new donor ID.
			$donor_id = $subscription->metadata->donor_id
			            ?? $this->get_repository( DonorRepository::class )
			                    ->find_one_by( [ 'vendor_customer_id' => $subscription->customerId ] )->id ?? null;

			// Save new transaction.
			$transaction = new TransactionEntity([
					'donor_id'        => $donor_id ?? '',
					'campaign_id'     => $campaign_id,
					'subscription_id' => $subscription_id ?? '',
					'vendor'          => self::get_slug()
				]			);
			$transaction_id = $transactions->insert($transaction);
			$transaction    = $transactions->get( $transaction_id );
		} else {
			/** @var TransactionEntity $transaction */
			$transaction = $transactions->get( (int) $payment->metadata->transaction_id );
		}

		/**
		 * We should have a transaction by now.
		 * To not leak any information to malicious third parties, it is recommended
		 * Always return a 200 OK response even if the ID is not known to your system.
		 *
		 * @link https://docs.mollie.com/overview/webhooks#how-to-handle-unknown-ids
		 */
		if ( ! $transaction ) {
			$this->logger->warning(
				'Webhook received for unknown transaction. Aborting',
				[ 'vendor_id' => $vendor_payment_id, 'transaction_id' => $payment->metadata->transaction_id ]
			);

			return false;
		}

		// Exit early if already processed
		if ( ! empty( $transaction->status ) && $transaction->status !== PaymentStatus::OPEN ) {
			$this->logger->debug( 'Duplicate handle_status_change call. Skipping.', [
				'payment_id'     => $vendor_payment_id,
				'transaction_id' => $transaction->id,
				'status'         => $transaction->status,
			] );

			return false;
		}

		if ( $payment->isPaid() && ! $payment->hasRefunds() && ! $payment->hasChargebacks() ) {
			// Update transaction.
			$transaction->fill([
				'status'            => $payment->status,
				'vendor_payment_id' => $payment->id,
				'value'             => floatval($payment->amount->value),
				'currency'          => $payment->amount->currency,
				'sequence_type'     => $payment->sequenceType,
				'method'            => $payment->method,
				'mode'              => $payment->mode
			]);
			$transactions->update($transaction);

			// Set up recurring payment if sequence is first.
			if ( $payment->hasSequenceTypeFirst() ) {
				$this->logger->info( 'Payment is initial subscription payment.', [ $transaction ] );
				$subscription_id = $this->create_subscription(
					$transaction,
					$payment->mandateId,
					$payment->metadata->frequency,
					(int) $payment->metadata->years
				);
				// Update transaction with subscription ID.
				$transaction->fill([
					'id'                     => $transaction->id,
					'subscription_id' => $subscription_id
				]);
				$transactions->update($transaction);
			}
		} elseif ( $payment->hasRefunds() ) {
			/*
			 * The payment has been (partially) refunded.
			 * The status of the payment is still "paid".
			 */
			do_action( 'kudos_mollie_refund', $transaction->id );

			// Update transaction.
			$transaction->status = $payment->status;
			$transaction->refunds = json_encode(
				[
					'refunded'  => $payment->getAmountRefunded(),
					'remaining' => $payment->getAmountRemaining(),
				]
			);
			$transactions->update($transaction);

			$this->logger->info( 'Payment refunded.', [
				'transaction' => $transaction,
				'refunded'    => $payment->getAmountRefunded(),
				'remaining'   => $payment->getAmountRemaining(),
			] );
		}

		// Create action with post id as parameter.
		do_action( "kudos_transaction_$payment->status", $transaction->id );

		return true;
	}

	/**
	 * Check the provided customer for valid mandates.
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
	 * {@inheritDoc}
	 */
	public function refund( int $entity_id ): bool {
		/** @var TransactionEntity $transaction */
		$transaction = $this->get_repository( TransactionRepository::class )->get( $entity_id );
		if ( $transaction ) {
			$payment_id         = $transaction->vendor_payment_id;
			$amount['value']    = Utils::format_value_for_use( $transaction->value );
			$amount['currency'] = $transaction->currency;
			try {
				$payment  = $this->api_client->payments->get( $payment_id );
				$response = $payment->refund( [ "amount" => $amount ] );
				$this->logger->info( sprintf( 'Refunding transaction "%s"', $payment_id ), [
					"status" => $response->status,
					'amount' => $amount
				] );
				if ( RefundStatus::PENDING == $response->status ) {
					return true;
				}

				return false;
			} catch ( RequestException $e ) {
				$this->logger->error( $e->getMessage() );
			}
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_settings(): array {
		return [
			self::SETTING_PROFILE => [
				'type' => FieldType::OBJECT,
				'show_in_rest' => [
					'schema' => [
						'properties' => [
							'id'            => [
								'type' => FieldType::STRING,
							],
							'mode' => [
								'type' => FieldType::STRING,
							],
							'name' => [
								'type' => FieldType::STRING
							],
							'website' => [
								'type' => FieldType::STRING
							],
							'status' => [
								'type' => FieldType::STRING
							]
						]
					]
				],
			],
			self::SETTING_API_MODE               => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => 'test'
			],
			self::SETTING_API_KEY_TEST           => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => ''
			],
			self::SETTING_API_KEY_LIVE           => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => ''
			],
			self::SETTING_API_KEY_ENCRYPTED_LIVE => [
				'type'         => FieldType::STRING,
				'show_in_rest' => false,
			],
			self::SETTING_API_KEY_ENCRYPTED_TEST => [
				'type'         => FieldType::STRING,
				'show_in_rest' => false,
			],
			self::SETTING_RECURRING              => [
				'type'         => FieldType::BOOLEAN,
				'show_in_rest' => true,
				'default'      => false
			],
			self::SETTING_PAYMENT_METHODS        => [
				'type'         => FieldType::ARRAY,
				'show_in_rest' => [
					'schema' => [
						'type'  => FieldType::ARRAY,
						'items' => [
							'type'       => FieldType::OBJECT,
							'properties' => [
								'id'            => [
									'type' => FieldType::STRING,
								],
								'description'   => [
									'type' => FieldType::STRING
								],
								'image'         => [
									'type' => FieldType::STRING
								],
								'minimumAmount' => [
									'type'       => FieldType::OBJECT,
									'properties' => [
										'value'    => [
											'type' => FieldType::STRING,
										],
										'currency' => [
											'type' => FieldType::STRING,
										],
									],
								],
								'maximumAmount' => [
									'type'       => FieldType::OBJECT,
									'properties' => [
										'value'    => [
											'type' => FieldType::STRING,
										],
										'currency' => [
											'type' => FieldType::STRING,
										],
									],
								],
							],
						],
					],
				],
				'default'      => []
			]
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function check_payment_status( string $payment_id ): ?string {
		try {
			$payment = $this->api_client->payments->get( $payment_id );

			return $payment->status;
		} catch ( RequestException $e ) {
			$this->logger->error( 'Error checking payment status', [ 'message' => $e->getMessage() ] );

			return null;
		}
	}
}
