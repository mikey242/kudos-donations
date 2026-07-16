<?php
/**
 * Mollie payment vendor.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 *
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Provider\PaymentProvider;

use Exception;
use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Exceptions\ApiException;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Exceptions\RequestException;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\MollieApiClient;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Resources\BaseCollection;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Resources\Customer;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Resources\Method;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Resources\MethodCollection;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\PaymentMethod;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\PaymentMethodStatus;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\PaymentStatus;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\RefundStatus;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\SequenceType;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\SubscriptionStatus;
use WP_REST_Request;
use WP_REST_Response;

class MolliePaymentProvider extends AbstractPaymentProvider {
	public const SETTING_CACHE                  = '_kudos_vendor_mollie_cache';
	public const SETTING_API_MODE               = '_kudos_vendor_mollie_api_mode';
	public const SETTING_API_KEY_LIVE           = '_kudos_vendor_mollie_api_key_live';
	public const SETTING_API_KEY_TEST           = '_kudos_vendor_mollie_api_key_test';
	public const SETTING_API_KEY_ENCRYPTED_LIVE = '_kudos_vendor_mollie_api_key_encrypted_live';
	public const SETTING_API_KEY_ENCRYPTED_TEST = '_kudos_vendor_mollie_api_key_encrypted_test';
	public MollieApiClient $api_client;
	private CampaignRepository $campaign_repository;
	private DonorRepository $donor_repository;
	private SubscriptionRepository $subscription_repository;

	/**
	 * Mollie constructor.
	 *
	 * @param MollieApiClient        $api_client Used to communicate with Mollie.
	 * @param CampaignRepository     $campaign_repository The campaign repository.
	 * @param TransactionRepository  $transaction_repository The transaction repository.
	 * @param DonorRepository        $donor_repository The donor repository.
	 * @param SubscriptionRepository $subscription_repository The subscription repository.
	 */
	public function __construct( MollieApiClient $api_client, CampaignRepository $campaign_repository, TransactionRepository $transaction_repository, DonorRepository $donor_repository, SubscriptionRepository $subscription_repository ) {
		$this->api_client              = $api_client;
		$this->campaign_repository     = $campaign_repository;
		$this->transaction_repository  = $transaction_repository;
		$this->donor_repository        = $donor_repository;
		$this->subscription_repository = $subscription_repository;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup(): void {
		$this->config_client();
		$this->set_user_agent();
		add_filter( 'pre_update_option_' . self::SETTING_API_KEY_LIVE, [ $this, 'handle_key_update' ], 10, 3 );
		add_filter( 'pre_update_option_' . self::SETTING_API_KEY_TEST, [ $this, 'handle_key_update' ], 10, 3 );
		add_action( 'update_option_' . self::SETTING_API_KEY_ENCRYPTED_LIVE, [ $this, 'handle_key_updated' ], 10, 2 );
		add_action( 'update_option_' . self::SETTING_API_KEY_ENCRYPTED_TEST, [ $this, 'handle_key_updated' ], 10, 2 );
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
	public static function get_icon_svg(): string {
		return '<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="320" height="320"><path d="M0 0h320v320H0z" style="fill:#000;stroke:none;stroke-width:3;paint-order:stroke fill markers"/><path d="M264.778 148.898c5.591 0 10.696 3.647 12.479 8.914h-24.878c1.702-5.186 6.726-8.914 12.399-8.914m24.877 13.452c0-6.483-2.512-12.641-7.13-17.341-4.62-4.7-10.778-7.293-17.261-7.293h-.324a25.7 25.7 0 0 0-17.909 7.536c-4.781 4.78-7.455 11.101-7.536 17.827-.081 6.888 2.593 13.371 7.455 18.314 4.943 4.943 11.426 7.698 18.314 7.698 9.076 0 17.584-4.862 22.203-12.641l.567-.972-10.21-5.025-.486.81c-2.512 4.215-6.969 6.646-11.912 6.646-6.24 0-11.67-4.133-13.37-10.13h37.6zM225.8 129.045a7.99 7.99 0 0 1-8.022-8.023A7.99 7.99 0 0 1 225.8 113a7.99 7.99 0 0 1 8.022 8.022c.081 4.376-3.565 8.023-8.022 8.023m-6.159 59.074h12.318v-49.35H219.64zm-23.58-74.066h12.317V188.2H196.06zm-23.582 74.066h12.318v-74.066h-12.318zm-32.495-11.102c-7.455 0-13.613-6.077-13.613-13.533s6.077-13.532 13.613-13.532c7.537 0 13.614 6.077 13.614 13.532 0 7.456-6.077 13.533-13.614 13.533m0-39.301c-14.262 0-25.768 11.506-25.768 25.687s11.587 25.77 25.768 25.77 25.77-11.507 25.77-25.689c0-14.18-11.507-25.768-25.77-25.768m-52.59.08c-.65-.08-1.297-.08-1.946-.08-6.24 0-12.155 2.512-16.369 7.05-4.213-4.457-10.129-7.05-16.288-7.05-12.398 0-22.446 10.048-22.446 22.284v28.119h12.074v-27.714c0-5.105 4.214-9.805 9.157-10.291.324 0 .73-.081 1.053-.081 5.592 0 10.13 4.538 10.13 10.13V188.2h12.317v-27.876c0-5.105 4.214-9.805 9.157-10.291.324 0 .73-.081 1.053-.081 5.592 0 10.13 4.538 10.21 10.048v28.119h12.318v-27.714c0-5.672-2.107-11.02-5.835-15.234-3.808-4.295-8.995-6.888-14.586-7.374" style="fill-rule:evenodd;clip-rule:evenodd;fill:#fff;stroke-width:.810345"/></svg>';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_cache_setting(): string {
		return self::SETTING_CACHE;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_status_extra( array $data ): array {
		return [ 'account' => $data['profile'] ?? '' ];
	}

	/**
	 * Returns the api mode.
	 */
	public function get_api_mode(): string {
		return get_option( self::SETTING_API_MODE, 'test' );
	}

	/**
	 * Change the API client to the key for the specified mode.
	 */
	protected function config_client(): bool {
		// Gets the key associated with the specified mode.
		$key = $this->get_api_key();

		if ( $key ) {
			try {
				$this->api_client->setApiKey( $key );
				return true;
			} catch ( Exception $e ) {
				$this->get_logger()->critical( $e->getMessage() );
			}
		}
		return false;
	}

	/**
	 * Sets the user agent for identifying requests made with this plugin.
	 *
	 * @see https://docs.mollie.com/docs/integration-partners-user-agent-strings
	 */
	private function set_user_agent(): void {
		global $wp_version;
		$this->api_client->addVersionString( 'KudosDonations/' . KUDOS_VERSION );
		$this->api_client->addVersionString( 'WordPress/' . $wp_version );
	}

	/**
	 * Uses get_payment_methods to determine if account can receive recurring payments.
	 */
	private function can_use_recurring(): bool {
		$methods = $this->get_active_payment_methods(
			[
				'sequenceType' => 'recurring',
			]
		);

		if ( $methods ) {
			return $methods->count() > 0;
		}

		return false;
	}

	/**
	 * Handles the saving of the test and live api keys.
	 *
	 * @param string $value The new value.
	 * @param string $_old_value The previous value.
	 * @param string $option The option name.
	 */
	public function handle_key_update( string $value, string $_old_value, string $option ): string {
		$mode             = ( self::SETTING_API_KEY_LIVE === $option ) ? 'live' : 'test';
		$encrypted_option = \constant( 'self::SETTING_API_KEY_ENCRYPTED_' . strtoupper( $mode ) );

		if ( ! $value ) {
			update_option( $encrypted_option, '' );
			$cache          = (array) get_option( self::SETTING_CACHE, [] );
			$cache[ $mode ] = [];
			update_option( self::SETTING_CACHE, $cache );
			return $value;
		}

		// Validate key format before saving. Mollie throws if the key prefix or
		// length is wrong, which would crash the site on subsequent page loads.
		try {
			$this->api_client->setApiKey( $value );
		} catch ( Exception $e ) {
			$this->get_logger()->error( 'Invalid Mollie API key: ' . $e->getMessage() );
			return $_old_value;
		}

		// Auto-set the mode to match the key being updated.
		update_option( self::SETTING_API_MODE, $mode );

		return $this->save_encrypted_key( $value, $encrypted_option );
	}

	/**
	 * {@inheritDoc}
	 */
	public function refresh(): bool {

		// Bail if unable to set api key with Mollie.
		if ( ! $this->config_client() ) {
			$this->get_logger()->error( 'Failed to set API key with Mollie' );
			return false;
		}

		$mode = $this->get_api_mode();

		$payment_methods = array_map(
			function ( Method $method ) {
				return [
					'id'          => $method->id,
					'description' => $method->description,
				];
			},
			(array) $this->get_active_payment_methods()
		);

		$this->get_logger()->debug( 'Mollie payment methods', $payment_methods );

		if ( empty( $payment_methods ) ) {
			$this->get_logger()->info( 'No payment methods found for Mollie' );
			return true;
		}

		try {
			$sepa = $this->api_client->methods->get( PaymentMethod::DIRECTDEBIT );
			if ( PaymentMethodStatus::ACTIVATED === $sepa->status ) {
				$payment_methods[] = [
					'id'          => $sepa->id,
					'description' => $sepa->description,
				];
			}
		} catch ( RequestException $e ) {
			$this->get_logger()->critical( 'Direct debit payment method not found', [ 'message' => $e->getMessage() ] );
		}

		$profile_name = '';
		try {
			$profile      = $this->api_client->profiles->getCurrent();
			$profile_name = $profile->name ?? '';
			$this->get_logger()->debug( 'Mollie profile fetched', [ $profile ] );
		} catch ( RequestException $e ) {
			$this->get_logger()->warning( 'Cannot get Mollie profile', [ 'message' => $e->getMessage() ] );
		}

		$this->get_logger()->debug( 'Mollie refreshed connection settings' );

		$cache          = (array) get_option( self::SETTING_CACHE, [] );
		$cache[ $mode ] = [
			'methods'   => $payment_methods,
			'recurring' => $this->can_use_recurring(),
			'profile'   => $profile_name,
		];
		update_option( self::SETTING_CACHE, $cache );

		return true;
	}

	/**
	 * Gets a list of payment methods for the current Mollie account
	 *
	 * @param array $options See https://docs.mollie.com/reference/v2/methods-api/list-methods.
	 * @return BaseCollection|MethodCollection|null
	 */
	public function get_active_payment_methods( array $options = [] ) {
		try {
			return $this->api_client->methods->allEnabled( $options );
		} catch ( RequestException $e ) {
			$this->get_logger()->critical( $e->getMessage() );

			return null;
		}
	}

	/**
	 * Cancel the specified subscription.
	 *
	 * @param SubscriptionEntity $subscription Instance of WP_Post.
	 */
	public function cancel_subscription( SubscriptionEntity $subscription ): bool {
		if ( null === $subscription->vendor_subscription_id ) {
			return false;
		}
		$vendor_customer_id = $subscription->vendor_customer_id ?? null;
		if ( null === $vendor_customer_id ) {
			return false;
		}
		$customer = $this->get_customer( $vendor_customer_id );

		// Bail if no subscription found locally or if not active.
		if ( SubscriptionStatus::ACTIVE !== $subscription->status || null === $customer ) {
			return false;
		}

		// Cancel the subscription via Mollie's API.
		try {
			$response = $customer->cancelSubscription( $subscription->vendor_subscription_id );
			$this->get_logger()->info( 'Mollie subscription cancelled', [ 'subscription' => $subscription ] );

			return ( PaymentStatus::CANCELED === $response->status );
		} catch ( ApiException $e ) {
			// Check if the subscription is already cancelled at Mollie's end before treating this as an error.
			try {
				$mollie_subscription = $customer->getSubscription( $subscription->vendor_subscription_id );
				if ( PaymentStatus::CANCELED === $mollie_subscription->status ) {
					$this->get_logger()->info( 'Subscription already cancelled with Mollie.', [ 'vendor_subscription_id' => $subscription->vendor_subscription_id ] );
					return true;
				}
            // phpcs:ignore
			} catch ( ApiException $inner ) {
				// Could not verify subscription status; fall through to error.
			}

			$this->get_logger()->error( 'Error cancelling subscription:', [ 'message' => $e->getMessage() ] );

			return false;
		}
	}

	/**
	 * Get the customer from Mollie.
	 *
	 * @param string $vendor_customer_id Mollie's customer ID.
	 */
	public function get_customer( string $vendor_customer_id ): ?Customer {
		try {
			return $this->api_client->customers->get( $vendor_customer_id );
		} catch ( RequestException $e ) {
			$this->get_logger()->critical( $e->getMessage() );

			return null;
		}
	}

	/**
	 * Create a Mollie customer.
	 *
	 * @param string $email Donor email address.
	 * @param string $name Donor name.
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
			$this->get_logger()->critical( $e->getMessage() );

			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function create_payment( array $payment_args, TransactionEntity $transaction, ?string $vendor_customer_id = null ) {

		$transaction_id = $transaction->id;

		// Set payment frequency.
		$is_recurring                      = 'true' === ( $payment_args['recurring'] ?? '' );
		$payment_args['payment_frequency'] = $is_recurring ? $payment_args['recurring_frequency'] : SequenceType::ONEOFF;
		$sequence_type                     = $is_recurring ? SequenceType::FIRST : SequenceType::ONEOFF;
		$payment_args['value']             = number_format( \floatval( $payment_args['value'] ), 2, '.', '' );
		$redirect_url                      = $payment_args['return_url'];

		// Create payment settings.
		$payment_array = [
			'amount'       => [
				'currency' => $payment_args['currency'],
				'value'    => $payment_args['value'],
			],
			'redirectUrl'  => $redirect_url,
			'webhookUrl'   => static::get_webhook_url(),
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
		if ( null !== $vendor_customer_id ) {
			$payment_array['customerId'] = $vendor_customer_id;
		}

		try {
			$payment = $this->api_client->payments->create( $payment_array );

			$this->get_logger()->info(
				'New ' . $this->get_name() . ' payment created.',
				[
					'transaction_id' => $transaction_id,
					'sequence_type'  => $payment->sequenceType,
				]
			);

			// Checkout URL used to complete payment.
			$checkout_url = $payment->getCheckoutUrl();

			// Update transaction entity from payment object.
			$transaction->checkout_url       = $checkout_url;
			$transaction->vendor_payment_id  = $payment->id;
			$transaction->vendor_customer_id = $payment->customerId;
			$this->transaction_repository
				->update( $transaction );

			return $checkout_url ?? false;
		} catch ( RequestException $e ) {
			$this->get_logger()->error( 'Error creating payment with Mollie', [ 'error' => $e->getMessage() ] );

			return false;
		}
	}

	/**
	 * Creates a subscription based on the provided transaction
	 *
	 * @param TransactionEntity $transaction The transaction entity object.
	 * @param string            $mandate_id Mollie mandate id.
	 * @param string            $interval How often the donation should occur.
	 * @param int               $years How many years the subscription should last for.
	 * @return false|int
	 */
	public function create_subscription( TransactionEntity $transaction, string $mandate_id, string $interval, int $years ) {
		$this->get_logger()->debug(
			'Creating subscription',
			[
				'mandate_id' => $mandate_id,
				'interval'   => $interval,
				'years'      => $years,
			]
		);

		// Bail if no donor id found on transaction.
		if ( null === $transaction->donor_id ) {
			$this->get_logger()->error( 'No donor found on transaction, aborting subscription creation', [ 'transaction' => $transaction ] );
			return false;
		}

		$vendor_customer_id = $transaction->vendor_customer_id;

		// Bail if no vendor customer id.
		if ( null === $vendor_customer_id ) {
			$this->get_logger()->error( 'No vendor id found on donor, aborting subscription creation', [ 'transaction' => $transaction ] );
			return false;
		}

		$start_date = gmdate( 'Y-m-d', strtotime( '+' . $interval ) );
		$currency   = $transaction->currency;
		$value      = Utils::format_value_for_use( $transaction->value );
		$customer   = $this->get_customer( $vendor_customer_id );

		if ( null === $customer ) {
			$this->get_logger()->error( 'Customer not found with Mollie, aborting subscription creation', [ 'vendor_customer_id' => $vendor_customer_id ] );
			return false;
		}

		// Create subscription if valid mandate found.
		if ( $this->check_mandate( $customer, $mandate_id ) ) {
			$this->get_logger()->debug( 'Customer has valid mandate, continuing.', [ 'mandate_id' => $mandate_id ] );
			try {

				$subscriptions = $this->subscription_repository;

				// Create subscription entity.
				$subscription_entity = $subscriptions->new_entity(
					[
						'frequency'      => $interval,
						'years'          => $years,
						'value'          => $value,
						'currency'       => $currency,
						'transaction_id' => $transaction->id,
						'donor_id'       => $transaction->donor_id,
						'campaign_id'    => $transaction->campaign_id,
						'vendor'         => self::get_slug(),
					]
				);
				$subscription_id     = $subscriptions->insert( $subscription_entity );
				$subscription_entity = $subscriptions->get( $subscription_id ); // Subscription entity needs to be re-fetched to get new title.

				if ( false === $subscription_id ) {
					$this->get_logger()->error( 'Error inserting subscription into database, aborting subscription creation', [ 'subscription_entity' => $subscription_entity ] );
					return false;
				}

				// Prepare arguments to send to Mollie.
				$subscription_args = [
					'amount'      => [
						'value'    => $value,
						'currency' => $currency,
					],
					'webhookUrl'  => static::get_webhook_url(),
					'mandateId'   => $mandate_id,
					'interval'    => $interval,
					'startDate'   => $start_date,
					'description' => $subscription_entity->title,
					'metadata'    => [
						'campaign_id'     => $transaction->campaign_id,
						'donor_id'        => $transaction->donor_id,
						'subscription_id' => $subscription_id,
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
				$this->get_logger()->debug( 'Subscription created with Mollie', [ 'result' => $subscription ] );

				// Update subscription post with status and subscription id.
				return $subscriptions->patch(
					$subscription_id,
					[
						'status'                 => $subscription->status,
						'vendor_customer_id'     => $subscription->customerId,
						'vendor_subscription_id' => $subscription->id,
					]
				) ? $subscription_id : false;
			} catch ( ApiException $e ) {
				$this->get_logger()->error(
					$e->getMessage(),
					[
						'transaction' => $transaction,
						'mandate_id'  => $mandate_id,
						'interval'    => $interval,
						'years'       => $years,
					]
				);

				return false;
			}
		}

		// No valid mandates.
		$this->get_logger()->error(
			__( 'Cannot create subscription as customer has no valid mandates.', 'kudos-donations' ),
			[ $vendor_customer_id ]
		);

		return false;
	}

	/**
	 * Mollie webhook handler.
	 *
	 * @param WP_REST_Request $request Request object.
	 */
	public function rest_webhook( WP_REST_Request $request ): WP_REST_Response {
		// Sanitize request params.
		$request->sanitize_params();

		// ID is case-sensitive (e.g: tr_HUW39xpdFN).
		$payment_id = $request->get_param( 'id' );
		// Log request.
		$this->get_logger()->info(
			'Webhook requested by ' . $this::get_name(),
			[
				'payment_id' => $payment_id,
			]
		);

		/**
		 * Create success response object.
		 *
		 * @link https://developer.wordpress.org/reference/classes/wp_rest_response/
		 */
		$response = new WP_REST_Response(
			[
				'success' => true,
				'id'      => $payment_id,
				'_links'  => [
					'self' => [
						'href' => rest_url( $request->get_route() ),
					],
				],
			]
		);

		// Process the payment asynchronously.
		if ( $payment_id ) {
			$this->enqueue_status_change_action( $payment_id );
		}

		return $response;
	}

	/**
	 * Mollie webhook handler.
	 *
	 * @throws RequestException If error communicating with Mollie.
	 *
	 * @param string $vendor_payment_id The Mollie payment id.
	 */
	public function handle_status_change( string $vendor_payment_id ): void {

		// Mollie API.
		$mollie = $this->api_client;

		/**
		 * Get the payment object from Mollie.
		 *
		 * @link https://docs.mollie.com/reference/v2/payments-api/get-payment
		 */
		$payment          = $mollie->payments->get( $vendor_payment_id );
		$payment_metadata = $payment->metadata;
		if ( ! \is_object( $payment_metadata ) ) {
			return;
		}

		// Log payment retrieval.
		$this->get_logger()->debug(
			'Payment retrieved from Mollie.',
			[
				'vendor_id'     => $vendor_payment_id,
				'status'        => $payment->status,
				'sequence_type' => $payment->sequenceType,
				'has_refunds'   => $payment->hasRefunds(),
				'metadata'      => $payment_metadata,
			]
		);

		// Get the transactions' repository.
		$transactions = $this->transaction_repository;

		/**
		 * Create new transaction if this is a recurring payment.
		 * e.g. New recurring payment.
		 */
		if ( $payment->hasSequenceTypeRecurring() ) {
			$this->get_logger()->debug(
				'Recurring payment received, creating transaction.',
				[
					'subscription_id' => $payment->subscriptionId,
				]
			);

			// Mollie delivers webhooks at least once, so skip payments we have already recorded.
			if ( null !== $this->transaction_repository->find_one_by( [ 'vendor_payment_id' => $vendor_payment_id ] ) ) {
				$this->get_logger()->debug( 'Duplicate recurring payment webhook. Skipping.', [ 'payment_id' => $vendor_payment_id ] );
				return;
			}

			// Bail if required properties not set.
			if ( null === $payment->customerId || null === $payment->subscriptionId ) {
				return;
			}

			$customer     = $mollie->customers->get( $payment->customerId );
			$subscription = $customer->getSubscription( $payment->subscriptionId );

			// Get post id if $campaign_id is slug from pre 4.0.0 version.
			$campaign_id = $subscription->metadata->campaign_id ?? null;
			$campaigns   = $this->campaign_repository;
			/** @var CampaignEntity|null $campaign */
			$campaign    = $campaigns
							->find_one_by( [ 'id' => $campaign_id ] ) ??
							$campaigns
							->find_one_by( [ 'wp_post_slug' => $campaign_id ] ) ?? null;
			$campaign_id = $campaign->id ?? null;

			// Subscription id.
			$subscription_id = $subscription->metadata->subscription_id ?? null;
			if ( null === $subscription_id ) {
				/** @var SubscriptionEntity|null $subscription_entity */
				$subscription_entity = $this->subscription_repository->find_one_by(
					[
						'vendor_subscription_id' => $subscription->id,
					]
				);
				$subscription_id     = $subscription_entity->id ?? null;
			}

			// Get Donor ID. If subscription from pre 4.0.0, use customerId to get new donor ID.
			$donor_id = $subscription->metadata->donor_id
						?? $this->donor_repository->find_one_by( [ 'vendor_customer_id' => $subscription->customerId ] )->id ?? null;

			// Save new transaction.
			$transaction    = new TransactionEntity(
				[
					'donor_id'           => null !== $donor_id ? (int) $donor_id : null,
					'campaign_id'        => $campaign_id,
					'subscription_id'    => $subscription_id,
					'vendor_customer_id' => $subscription->customerId,
					'vendor'             => self::get_slug(),
				]
			);
			$transaction_id = $transactions->insert( $transaction );

			if ( false === $transaction_id ) {
				return;
			}

			$transaction = $transactions->get( $transaction_id );
		} else {
			$transaction_id = $payment_metadata->transaction_id;

			/** @var TransactionEntity $transaction */
			$transaction = $transactions->get( (int) $transaction_id );
		}

		/**
		 * We should have a transaction by now.
		 * To not leak any information to malicious third parties, it is recommended
		 * Always return a 200 OK response even if the ID is not known to your system.
		 *
		 * @link https://docs.mollie.com/overview/webhooks#how-to-handle-unknown-ids
		 */
		if ( ! $transaction ) {
			$transaction_id = $payment_metadata->transaction_id ?? null;
			$this->get_logger()->warning(
				'Webhook received for unknown transaction. Aborting',
				[
					'vendor_id'      => $vendor_payment_id,
					'transaction_id' => $transaction_id,
				]
			);

			return;
		}

		// Exit early if already processed.
		if ( ! empty( $transaction->status ) && PaymentStatus::OPEN !== $transaction->status ) {
			$this->get_logger()->debug(
				'Duplicate handle_status_change call. Skipping.',
				[
					'payment_id'     => $vendor_payment_id,
					'transaction_id' => $transaction->id,
					'status'         => $transaction->status,
				]
			);

			return;
		}

		if ( $payment->isPaid() && ! $payment->hasRefunds() && ! $payment->hasChargebacks() ) {
			// Update transaction.
			$transaction->status             = $payment->status;
			$transaction->vendor_payment_id  = $payment->id;
			$transaction->vendor_customer_id = $payment->customerId;
			$transaction->value              = \floatval( $payment->amount->value );
			$transaction->currency           = $payment->amount->currency;
			$transaction->sequence_type      = $payment->sequenceType;
			$transaction->method             = $payment->method;
			$transaction->mode               = $payment->mode;

			$transactions->update( $transaction );

			// Set up recurring payment if sequence is first.
			if ( $payment->hasSequenceTypeFirst() ) {
				$this->get_logger()->info( 'Payment is initial subscription payment.', [ $transaction ] );

				if ( null === $payment->mandateId ) {
					return;
				}

				$subscription_id = $this->create_subscription(
					$transaction,
					$payment->mandateId,
					$payment_metadata->frequency,
					(int) $payment_metadata->years
				);

				// Bail if failed to create subscription.
				if ( false === $subscription_id ) {
					$this->get_logger()->error( 'Failed to create subscription.' );
					return;
				}

				// Update transaction with subscription ID.
				$transaction->subscription_id = $subscription_id;
				$transactions->update( $transaction );
			}
		} elseif ( $payment->hasRefunds() ) {
			/*
			 * The payment has been (partially/fully) refunded.
			 * The status of the payment is still "paid".
			 */
			$this->on_transaction_refunded( $transaction );

			// Update transaction.
			$transaction->status  = $payment->status;
			$transaction->refunds = wp_json_encode(
				[
					'refunded'  => $payment->getAmountRefunded(),
					'remaining' => $payment->getAmountRemaining(),
				]
			);
			$transactions->update( $transaction );

			$this->get_logger()->info(
				'Payment refunded.',
				[
					'transaction' => $transaction,
					'refunded'    => $payment->getAmountRefunded(),
					'remaining'   => $payment->getAmountRemaining(),
				]
			);
		}

		$this->on_transaction_status_changed( $transaction );
	}

	/**
	 * Check the provided customer for valid mandates.
	 *
	 * @param Customer $customer The customer object.
	 * @param string   $mandate_id The mandate id to check.
	 */
	private function check_mandate( Customer $customer, string $mandate_id ): bool {
		try {
			$mandate = $customer->getMandate( $mandate_id );
			if ( $mandate->isValid() || $mandate->isPending() ) {
				return true;
			}
		} catch ( ApiException $e ) {
			$this->get_logger()->error( $e->getMessage() );
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function refund( int $entity_id ): bool {
		/** @var TransactionEntity|null $transaction */
		$transaction = $this->transaction_repository->get( $entity_id );
		$amount      = [];
		if ( null !== $transaction ) {
			$payment_id = $transaction->vendor_payment_id;

			if ( null === $payment_id ) {
				return false;
			}

			$amount['value']    = Utils::format_value_for_use( $transaction->value );
			$amount['currency'] = $transaction->currency;
			try {
				$payment  = $this->api_client->payments->get( $payment_id );
				$response = $payment->refund( [ 'amount' => $amount ] );
				$this->get_logger()->info(
					\sprintf( 'Refunding transaction "%s"', $payment_id ),
					[
						'status' => $response->status,
						'amount' => $amount,
					]
				);
				if ( RefundStatus::PENDING === $response->status ) {
					return true;
				}

				return false;
			} catch ( RequestException $e ) {
				$this->get_logger()->error( $e->getMessage() );
			}
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_settings(): array {
		return [
			self::SETTING_CACHE                  => [
				'type'         => FieldType::OBJECT,
				'show_in_rest' => false,
				'default'      => [],
			],
			self::SETTING_API_MODE               => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => 'test',
			],
			self::SETTING_API_KEY_TEST           => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => '',
			],
			self::SETTING_API_KEY_LIVE           => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => '',
			],
			self::SETTING_API_KEY_ENCRYPTED_LIVE => [
				'type'         => FieldType::STRING,
				'show_in_rest' => false,
			],
			self::SETTING_API_KEY_ENCRYPTED_TEST => [
				'type'         => FieldType::STRING,
				'show_in_rest' => false,
			],
		];
	}
}
