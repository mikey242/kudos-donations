<?php
/**
 * Stripe payment provider.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 *
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Provider\PaymentProvider;

use IseardMedia\Kudos\Domain\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Helper\Localization;
use IseardMedia\Kudos\Service\NoticeService;
use IseardMedia\Kudos\ThirdParty\Stripe\Capability;
use IseardMedia\Kudos\ThirdParty\Stripe\Checkout\Session;
use IseardMedia\Kudos\ThirdParty\Stripe\Event;
use IseardMedia\Kudos\ThirdParty\Stripe\Exception\ApiErrorException;
use IseardMedia\Kudos\ThirdParty\Stripe\Exception\SignatureVerificationException;
use IseardMedia\Kudos\ThirdParty\Stripe\Exception\UnexpectedValueException;
use IseardMedia\Kudos\ThirdParty\Stripe\Invoice;
use IseardMedia\Kudos\ThirdParty\Stripe\Stripe;
use IseardMedia\Kudos\ThirdParty\Stripe\StripeClient;
use IseardMedia\Kudos\ThirdParty\Stripe\Subscription;
use IseardMedia\Kudos\ThirdParty\Stripe\Webhook;
use WP_REST_Request;
use WP_REST_Response;

class StripePaymentProvider extends AbstractPaymentProvider {
	public const SETTING_CACHE                  = '_kudos_vendor_stripe_cache';
	public const SETTING_API_MODE               = '_kudos_vendor_stripe_api_mode';
	public const SETTING_API_KEY_TEST           = '_kudos_vendor_stripe_api_key_test';
	public const SETTING_API_KEY_LIVE           = '_kudos_vendor_stripe_api_key_live';
	public const SETTING_API_KEY_ENCRYPTED_TEST = '_kudos_vendor_stripe_api_key_encrypted_test';
	public const SETTING_API_KEY_ENCRYPTED_LIVE = '_kudos_vendor_stripe_api_key_encrypted_live';
	public const SETTING_WEBHOOK                = '_kudos_vendor_stripe_webhook';

	private const CAPABILITY_LABELS = [
		'blik_payments'              => 'BLIK',
		'card_payments'              => 'Card',
		'link_payments'              => 'Link',
		'ideal_payments'             => 'iDEAL',
		'sepa_debit_payments'        => 'SEPA Direct Debit',
		'bancontact_payments'        => 'Bancontact',
		'transfers'                  => 'Bank Transfers',
		'klarna_payments'            => 'Klarna',
		'sofort_payments'            => 'Sofort',
		'giropay_payments'           => 'Giropay',
		'eps_payments'               => 'EPS',
		'p24_payments'               => 'Przelewy24',
		'afterpay_clearpay_payments' => 'Afterpay / Clearpay',
		'acss_debit_payments'        => 'Pre-authorized debits (CA)',
		'bacs_debit_payments'        => 'BACS Direct Debit',
		'au_becs_debit_payments'     => 'BECS Direct Debit (AU)',
		'boleto_payments'            => 'Boleto',
		'fpx_payments'               => 'FPX',
		'grabpay_payments'           => 'GrabPay',
		'oxxo_payments'              => 'OXXO',
		'alipay_payments'            => 'Alipay',
		'paynow_payments'            => 'PayNow',
		'promptpay_payments'         => 'PromptPay',
		'pix_payments'               => 'Pix',
		'konbini_payments'           => 'Konbini',
		'revolut_pay_payments'       => 'Revolut Pay',
		'amazon_pay_payments'        => 'Amazon Pay',
		'twint_payments'             => 'TWINT',
	];

	private ?StripeClient $stripe = null;
	private SubscriptionRepository $subscription_repository;

	/**
	 * StripePaymentProvider constructor.
	 *
	 * @param TransactionRepository  $transaction_repository The transaction repository.
	 * @param SubscriptionRepository $subscription_repository The subscription repository.
	 */
	public function __construct( TransactionRepository $transaction_repository, SubscriptionRepository $subscription_repository ) {
		$this->transaction_repository  = $transaction_repository;
		$this->subscription_repository = $subscription_repository;
	}

	/**
	 * Returns a StripeClient initialised with the current mode's decrypted key,
	 * or null if no key is stored yet. Memorised for the lifetime of the request.
	 */
	private function get_client(): ?StripeClient {
		if ( null !== $this->stripe ) {
			return $this->stripe;
		}
		$key = $this->get_api_key();
		if ( ! $key ) {
			return null;
		}
		$this->stripe = new StripeClient( $key );
		return $this->stripe;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup(): void {
		Localization::add_admin( 'stripeWebhookUrl', self::get_webhook_url() );
		Stripe::setEnableTelemetry( false );
		Stripe::setLogger( $this->get_logger() );
		add_action( 'kudos_stripe_handle_status_change', [ $this, 'handle_status_change' ] );
		add_filter( 'pre_update_option_' . self::SETTING_API_KEY_LIVE, [ $this, 'handle_key_update' ], 10, 3 );
		add_filter( 'pre_update_option_' . self::SETTING_API_KEY_TEST, [ $this, 'handle_key_update' ], 10, 3 );
		add_action( 'update_option_' . self::SETTING_API_KEY_ENCRYPTED_LIVE, [ $this, 'handle_key_updated' ], 10, 2 );
		add_action( 'update_option_' . self::SETTING_API_KEY_ENCRYPTED_TEST, [ $this, 'handle_key_updated' ], 10, 2 );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_name(): string {
		return 'Stripe';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'stripe';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_icon_svg(): string {
		return '<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 400 400"><path d="M0 0h400v400H0z" style="fill-rule:evenodd;clip-rule:evenodd;fill:#635bff"/><path d="M184.4 155.5c0-9.4 7.7-13.1 20.5-13.1 18.4 0 41.6 5.6 60 15.5v-56.8C244.8 93.1 225 90 205 90c-49.1 0-81.7 25.6-81.7 68.4 0 66.7 91.9 56.1 91.9 84.9 0 11.1-9.7 14.7-23.2 14.7-20.1 0-45.7-8.2-66-19.3v57.5c22.5 9.7 45.2 13.8 66 13.8 50.3 0 84.9-24.9 84.9-68.2-.4-72-92.5-59.2-92.5-86.3" style="fill-rule:evenodd;clip-rule:evenodd;fill:#fff"/></svg>';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function is_enabled(): bool {
		return true;
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
	public function get_api_mode(): string {
		return get_option( self::SETTING_API_MODE, 'test' );
	}

	/**
	 * Handles the saving of the test and live API keys.
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
			update_option( self::SETTING_WEBHOOK, [] );
			return $value;
		}

		$valid_prefixes = [ 'rk_' . $mode . '_', 'sk_' . $mode . '_' ];
		$is_valid       = false;
		foreach ( $valid_prefixes as $prefix ) {
			if ( strpos( $value, $prefix ) === 0 ) {
				$is_valid = true;
				break;
			}
		}
		if ( ! $is_valid ) {
			$this->get_logger()->error( 'Invalid Stripe API key: must start with "rk_' . $mode . '_" or "sk_' . $mode . '_".' );
			return $_old_value;
		}

		update_option( self::SETTING_API_MODE, $mode );

		return $this->save_encrypted_key( $value, $encrypted_option );
	}

	/**
	 * Triggers a refresh after a key option is successfully saved.
	 *
	 * @param string $old_value Previous option value.
	 * @param string $new_value New option value (starred or empty).
	 */
	public function handle_key_updated( string $old_value, string $new_value ): void {
		if ( ! $new_value ) {
			return;
		}
		$this->refresh();
	}

	/**
	 * {@inheritDoc}
	 */
	public function refresh(): bool {
		$key = $this->get_api_key();

		if ( ! $key ) {
			return false;
		}

		$this->register_webhook();

		$mode           = $this->get_api_mode();
		$cache          = (array) get_option( self::SETTING_CACHE, [] );
		$cache[ $mode ] = [
			'methods'   => $this->get_payment_methods(),
			'recurring' => $this->can_use_recurring(),
		];
		update_option( self::SETTING_CACHE, $cache );

		return true;
	}

	/**
	 * Stripe subscriptions are available to all accounts — return true if the client is configured.
	 */
	private function can_use_recurring(): bool {
		return null !== $this->get_client();
	}

	/**
	 * Converts a frequency string (e.g. '1 month', '3 months', '12 months')
	 * into a Stripe recurring interval array.
	 *
	 * @param string $frequency The frequency string.
	 * @return array{interval: string, interval_count: int}
	 */
	private function parse_frequency( string $frequency ): array {
		$parts = explode( ' ', trim( $frequency ) );
		return [
			'interval'       => rtrim( $parts[1], 's' ),
			'interval_count' => (int) ( $parts[0] ),
		];
	}

	/**
	 * Creates a local subscription entity linked to the given Stripe subscription ID.
	 *
	 * @param TransactionEntity $transaction The initial transaction.
	 * @param string            $stripe_subscription_id The Stripe subscription ID.
	 * @param string            $frequency The frequency string (e.g. '1 month').
	 * @param int               $years How many years the subscription should run (0 = indefinite).
	 * @return int|false The new subscription entity ID, or false on failure.
	 */
	private function create_subscription( TransactionEntity $transaction, string $stripe_subscription_id, string $frequency, int $years ) {
		$subscription_entity = $this->subscription_repository->new_entity(
			[
				'frequency'              => $frequency,
				'years'                  => $years ? $years : null,
				'value'                  => $transaction->value,
				'currency'               => $transaction->currency,
				'transaction_id'         => $transaction->id,
				'donor_id'               => $transaction->donor_id,
				'campaign_id'            => $transaction->campaign_id,
				'vendor'                 => self::get_slug(),
				'status'                 => Subscription::STATUS_ACTIVE,
				'vendor_subscription_id' => $stripe_subscription_id,
				'vendor_customer_id'     => $transaction->vendor_customer_id,
			]
		);

		$subscription_id = $this->subscription_repository->insert( $subscription_entity );

		if ( false === $subscription_id ) {
			$this->get_logger()->error( 'Failed to insert Stripe subscription entity.', [ 'transaction_id' => $transaction->id ] );
			return false;
		}

		$this->get_logger()->info(
			'Stripe subscription entity created.',
			[
				'subscription_id'        => $subscription_id,
				'stripe_subscription_id' => $stripe_subscription_id,
			]
		);

		return $subscription_id;
	}

	/**
	 * Fetches payment method capabilities from the Stripe account.
	 *
	 * @return array<int, array{id: string, description: string, status: string}>
	 */
	private function get_payment_methods(): array {
		$client = $this->get_client();
		if ( null === $client ) {
			return [];
		}

		try {
			$account      = $client->accounts->retrieve();
			$capabilities = $account->capabilities->toArray();
		} catch ( ApiErrorException $e ) {
			$this->get_logger()->warning( 'Could not fetch Stripe account capabilities.', [ 'error' => $e->getMessage() ] );
			return [];
		}

		$methods = [];
		foreach ( $capabilities as $key => $status ) {
			if ( Capability::STATUS_ACTIVE === $status ) {
				$methods[] = [
					'id'          => $key,
					'description' => self::CAPABILITY_LABELS[ $key ] ?? '',
					'status'      => (string) $status,
				];
			}
		}

		return $methods;
	}

	/**
	 * Attempts to auto-register a Stripe webhook endpoint and store the signing secret.
	 * If registration fails (e.g. insufficient key permissions), stores an admin notice
	 * directing the user to configure it manually.
	 */
	private function register_webhook(): void {
		$webhook = (array) get_option( self::SETTING_WEBHOOK, [] );
		if ( ! empty( $webhook['secret'] ) ) {
			return;
		}

		$client = $this->get_client();
		if ( null === $client ) {
			return;
		}

		$webhook_url = self::get_webhook_url();

		try {
			$endpoint = $client->webhookEndpoints->create(
				[
					'url'            => $webhook_url,
					'enabled_events' => [
						'checkout.session.completed',
						'checkout.session.expired',
						'invoice.payment_succeeded',
					],
				]
			);

			update_option(
				self::SETTING_WEBHOOK,
				[
					'secret'      => $endpoint->secret,
					'endpoint_id' => $endpoint->id,
				]
			);

			$this->get_logger()->info( 'Stripe webhook endpoint registered.', [ 'endpoint_id' => $endpoint->id ] );
		} catch ( ApiErrorException $e ) {
			$this->get_logger()->warning( 'Could not auto-register Stripe webhook endpoint.', [ 'error' => $e->getMessage() ] );
			NoticeService::add_notice(
				\sprintf(
					// translators: %1$s is the webhook URL. %2$s is the admin URL.
					__( 'Stripe webhook could not be registered automatically. Add <code>%1$s</code> as a webhook endpoint in your <a href="https://dashboard.stripe.com/webhooks">Stripe Dashboard</a>, then paste the signing secret into the <a href="%2$s">Stripe settings</a>.', 'kudos-donations' ),
					esc_url( $webhook_url ),
					esc_url( admin_url( 'admin.php?page=kudos-settings&tab=payment&panel=webhook' ) ),
				),
				NoticeService::WARNING,
				true,
				'stripe-webhook-registration-failed'
			);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function cancel_subscription( SubscriptionEntity $subscription ): bool {
		if ( null === $subscription->vendor_subscription_id ) {
			return false;
		}

		$client = $this->get_client();
		if ( null === $client ) {
			return false;
		}

		try {
			$result = $client->subscriptions->cancel( $subscription->vendor_subscription_id );
			$this->get_logger()->info( 'Stripe subscription cancelled.', [ 'vendor_subscription_id' => $subscription->vendor_subscription_id ] );
			return Subscription::STATUS_CANCELED === $result->status;
		} catch ( ApiErrorException $e ) {
			$this->get_logger()->error( 'Error cancelling Stripe subscription.', [ 'error' => $e->getMessage() ] );
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function create_customer( string $email, string $name ) {
		$client = $this->get_client();
		if ( null === $client ) {
			return false;
		}
		try {
			return $client->customers->create(
				[
					'name'        => $name,
					'email'       => $email,
					'description' => __( 'Kudos Donations Donor', 'kudos-donations' ),
				]
			);
		} catch ( ApiErrorException $e ) {
			$this->get_logger()->critical( $e->getMessage() );
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function create_payment( array $payment_args, TransactionEntity $transaction, ?string $vendor_customer_id = null ) {
		$client = $this->get_client();
		if ( null === $client ) {
			return false;
		}

		$value  = number_format( \floatval( $payment_args['value'] ), 2, '.', '' );
		$amount = (int) round( \floatval( $value ) * 100 ); // Stripe expects smallest currency unit.

		$price_data = [
			'currency'     => strtolower( (string) $payment_args['currency'] ),
			'unit_amount'  => $amount,
			'product_data' => [
				'name' => $transaction->title,
			],
		];

		$is_recurring = 'true' === ( $payment_args['recurring'] ?? '' );
		if ( $is_recurring ) {
			$price_data['recurring'] = $this->parse_frequency( (string) ( $payment_args['recurring_frequency'] ?? '1 month' ) );
		}

		$session_args = [
			'mode'        => $is_recurring ? Session::MODE_SUBSCRIPTION : Session::MODE_PAYMENT,
			'line_items'  => [
				[
					'price_data' => $price_data,
					'quantity'   => 1,
				],
			],
			'success_url' => (string) $payment_args['return_url'],
			'cancel_url'  => (string) $payment_args['return_url'],
			'metadata'    => [
				'transaction_id'      => (string) $transaction->id,
				'campaign_id'         => (string) $payment_args['campaign_id'],
				'email'               => (string) $payment_args['email'],
				'name'                => (string) $payment_args['name'],
				'recurring_frequency' => (string) ( $payment_args['recurring_frequency'] ?? '' ),
				'recurring_length'    => (string) ( $payment_args['recurring_length'] ?? '0' ),
			],
		];

		if ( null !== $vendor_customer_id ) {
			$session_args['customer'] = $vendor_customer_id;
		}

		try {
			$session = $client->checkout->sessions->create( $session_args );

			$transaction->checkout_url      = $session->url;
			$transaction->vendor_payment_id = $session->id;
			$this->transaction_repository->update( $transaction );

			$this->get_logger()->info(
				'New Stripe Checkout Session created.',
				[
					'transaction_id' => $transaction->id,
					'session_id'     => $session->id,
				]
			);

			return $session->url ?? false;
		} catch ( ApiErrorException $e ) {
			$this->get_logger()->error( 'Error creating Stripe Checkout Session', [ 'error' => $e->getMessage() ] );
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function refund( int $entity_id ): bool {
		$client = $this->get_client();
		if ( null === $client ) {
			return false;
		}

		/** @var TransactionEntity|null $transaction */
		$transaction = $this->transaction_repository->get( $entity_id );
		if ( null === $transaction || null === $transaction->vendor_payment_id ) {
			return false;
		}

		try {
			if ( str_starts_with( $transaction->vendor_payment_id, 'in_' ) ) {
				$invoice   = $client->invoices->retrieve( $transaction->vendor_payment_id );
				$charge_id = $invoice->charge ?? null;
				if ( ! $charge_id ) {
					$this->get_logger()->error( 'No charge on Stripe invoice', [ 'invoice_id' => $transaction->vendor_payment_id ] );
					return false;
				}
				$refund = $client->refunds->create( [ 'charge' => $charge_id ] );
			} else {
				$session           = $client->checkout->sessions->retrieve( $transaction->vendor_payment_id );
				$payment_intent_id = $session->payment_intent;
				if ( ! $payment_intent_id ) {
					$this->get_logger()->error( 'No payment_intent on Stripe session', [ 'session_id' => $transaction->vendor_payment_id ] );
					return false;
				}
				$refund = $client->refunds->create( [ 'payment_intent' => $payment_intent_id ] );
			}

			$this->get_logger()->info(
				'Stripe refund created.',
				[
					'status'     => $refund->status,
					'payment_id' => $transaction->vendor_payment_id,
				]
			);
			return \in_array( $refund->status, [ 'succeeded', 'pending' ], true );
		} catch ( ApiErrorException $e ) {
			$this->get_logger()->error( 'Error creating Stripe refund', [ 'error' => $e->getMessage() ] );
			return false;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function rest_webhook( WP_REST_Request $request ): WP_REST_Response {
		$payload    = $request->get_body();
		$sig_header = $request->get_header( 'stripe_signature' );
		$webhook    = (array) get_option( self::SETTING_WEBHOOK, [] );
		$secret     = (string) ( $webhook['secret'] ?? '' );

		if ( ! $secret ) {
			$this->get_logger()->error( 'Stripe webhook secret not configured.' );
			return new WP_REST_Response(
				[
					'success' => false,
					'message' => 'Webhook secret not configured.',
				],
				400
			);
		}

		try {
			$event = Webhook::constructEvent( $payload, $sig_header, $secret );
		} catch ( SignatureVerificationException $e ) {
			$this->get_logger()->warning( 'Stripe webhook signature verification failed', [ 'error' => $e->getMessage() ] );
			return new WP_REST_Response(
				[
					'success' => false,
					'message' => 'Signature verification failed.',
				],
				400
			);
		} catch ( UnexpectedValueException $e ) {
			$this->get_logger()->warning( 'Stripe webhook invalid payload', [ 'error' => $e->getMessage() ] );
			return new WP_REST_Response(
				[
					'success' => false,
					'message' => 'Invalid payload.',
				],
				400
			);
		}

		$this->get_logger()->info( 'Stripe webhook received.', [ 'type' => $event->type ] );

		$handled_events = [ Event::CHECKOUT_SESSION_COMPLETED, Event::CHECKOUT_SESSION_EXPIRED, Event::INVOICE_PAYMENT_SUCCEEDED ];
		if ( \in_array( $event->type, $handled_events, true ) ) {
			$this->enqueue_status_change_action( $event->data->object->id );
		}

		return new WP_REST_Response( [ 'success' => true ], 200 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle_status_change( string $vendor_payment_id ): void {
		if ( str_starts_with( $vendor_payment_id, 'in_' ) ) {
			$this->handle_invoice_payment( $vendor_payment_id );
			return;
		}

		$client = $this->get_client();
		if ( null === $client ) {
			return;
		}

		try {
			$session = $client->checkout->sessions->retrieve( $vendor_payment_id );
		} catch ( ApiErrorException $e ) {
			$this->get_logger()->error(
				'Error retrieving Stripe Checkout Session',
				[
					'error'      => $e->getMessage(),
					'session_id' => $vendor_payment_id,
				]
			);
			return;
		}

		$transaction_id = $session->metadata->transaction_id ?? null;
		if ( null === $transaction_id ) {
			$this->get_logger()->warning( 'No transaction_id in Stripe session metadata', [ 'session_id' => $vendor_payment_id ] );
			return;
		}

		/** @var TransactionEntity|null $transaction */
		$transaction = $this->transaction_repository->get( (int) $transaction_id );
		if ( null === $transaction ) {
			$this->get_logger()->warning(
				'Transaction not found for Stripe session.',
				[
					'transaction_id' => $transaction_id,
					'session_id'     => $vendor_payment_id,
				]
			);
			return;
		}

		// Exit early if already processed.
		if ( ! empty( $transaction->status ) && PaymentStatus::OPEN !== $transaction->status ) {
			$this->get_logger()->debug(
				'Duplicate handle_status_change call. Skipping.',
				[
					'session_id'     => $vendor_payment_id,
					'transaction_id' => $transaction->id,
					'status'         => $transaction->status,
				]
			);
			return;
		}

		if ( Session::STATUS_COMPLETE === $session->status && Session::PAYMENT_STATUS_PAID === $session->payment_status ) {
			$transaction->status            = PaymentStatus::PAID;
			$transaction->vendor_payment_id = $session->id;
			$transaction->mode              = $session->livemode ? 'live' : 'test';

			$stripe_subscription_id = $session->subscription ?? null;
			if ( $stripe_subscription_id && 'first' === $transaction->sequence_type ) {
				$frequency = (string) ( $session->metadata->recurring_frequency ?? '1 month' );
				$years     = (int) ( $session->metadata->recurring_length ?? 0 );

				$subscription_id = $this->create_subscription( $transaction, $stripe_subscription_id, $frequency, $years );
				if ( false !== $subscription_id ) {
					$transaction->subscription_id = $subscription_id;
				}
			}

			$this->transaction_repository->update( $transaction );
			$this->on_transaction_status_changed( $transaction );
		} elseif ( Session::STATUS_EXPIRED === $session->status ) {
			$transaction->status = PaymentStatus::EXPIRED;
			$this->transaction_repository->update( $transaction );
			$this->on_transaction_status_changed( $transaction );
		}
	}

	/**
	 * Handles a Stripe invoice.payment_succeeded event for recurring subscription charges.
	 *
	 * @param string $invoice_id The Stripe invoice ID.
	 */
	private function handle_invoice_payment( string $invoice_id ): void {
		$client = $this->get_client();
		if ( null === $client ) {
			return;
		}

		try {
			$invoice = $client->invoices->retrieve( $invoice_id );
		} catch ( ApiErrorException $e ) {
			$this->get_logger()->error(
				'Error retrieving Stripe invoice.',
				[
					'error'      => $e->getMessage(),
					'invoice_id' => $invoice_id,
				]
			);
			return;
		}

		// The initial subscription payment is already handled by checkout.session.completed.
		if ( Invoice::BILLING_REASON_SUBSCRIPTION_CYCLE !== $invoice->billing_reason ) {
			return;
		}

		$stripe_subscription_id = $invoice->parent->subscription_details->subscription ?? null;
		if ( ! $stripe_subscription_id ) {
			$this->get_logger()->warning( 'No subscription on Stripe invoice.', [ 'invoice_id' => $invoice_id ] );
			return;
		}

		/** @var SubscriptionEntity|null $subscription */
		$subscription = $this->subscription_repository->find_one_by( [ 'vendor_subscription_id' => $stripe_subscription_id ] );
		if ( null === $subscription ) {
			$this->get_logger()->warning( 'No local subscription found for Stripe subscription.', [ 'stripe_subscription_id' => $stripe_subscription_id ] );
			return;
		}

		$transaction = $this->transaction_repository->new_entity(
			[
				'donor_id'           => $subscription->donor_id,
				'campaign_id'        => $subscription->campaign_id,
				'subscription_id'    => $subscription->id,
				'vendor_customer_id' => $invoice->customer,
				'vendor_payment_id'  => $invoice->id,
				'vendor'             => self::get_slug(),
				'status'             => PaymentStatus::PAID,
				'value'              => round( $invoice->amount_paid / 100, 2 ),
				'currency'           => strtoupper( $invoice->currency ),
				'sequence_type'      => 'recurring',
				'mode'               => $invoice->livemode ? 'live' : 'test',
			]
		);

		$transaction_id = $this->transaction_repository->insert( $transaction );
		if ( false === $transaction_id ) {
			$this->get_logger()->error( 'Failed to insert recurring Stripe transaction.', [ 'invoice_id' => $invoice_id ] );
			return;
		}

		$transaction = $this->transaction_repository->get( $transaction_id );
		if ( null === $transaction ) {
			$this->get_logger()->error( 'Failed to re-fetch inserted recurring transaction.', [ 'transaction_id' => $transaction_id ] );
			return;
		}
		$this->get_logger()->info(
			'Recurring Stripe payment recorded.',
			[
				'transaction_id' => $transaction_id,
				'invoice_id'     => $invoice_id,
			]
		);
		$this->on_transaction_status_changed( $transaction );
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
			self::SETTING_API_KEY_ENCRYPTED_TEST => [
				'type'         => FieldType::STRING,
				'show_in_rest' => false,
			],
			self::SETTING_API_KEY_ENCRYPTED_LIVE => [
				'type'         => FieldType::STRING,
				'show_in_rest' => false,
			],
			self::SETTING_WEBHOOK                => [
				'type'         => FieldType::OBJECT,
				'show_in_rest' => [
					'schema' => [
						'properties' => [
							'secret'      => [ 'type' => FieldType::STRING ],
							'endpoint_id' => [ 'type' => FieldType::STRING ],
						],
					],
				],
				'default'      => [],
			],
		];
	}
}
