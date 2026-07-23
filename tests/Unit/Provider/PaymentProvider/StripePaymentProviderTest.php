<?php

namespace IseardMedia\Kudos\Tests\Provider\PaymentProvider;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Provider\PaymentProvider\StripePaymentProvider;
use IseardMedia\Kudos\Tests\BaseTestCase;
use IseardMedia\Kudos\Tests\Stubs\FakeStripeHttpClient;
use IseardMedia\Kudos\ThirdParty\Stripe\ApiRequestor;
use IseardMedia\Kudos\ThirdParty\Stripe\Checkout\Session;
use IseardMedia\Kudos\ThirdParty\Stripe\StripeClient;
use Psr\Log\LoggerInterface;
use ReflectionProperty;
use WP_REST_Request;

/**
 * @covers \IseardMedia\Kudos\Provider\PaymentProvider\StripePaymentProvider
 */
class StripePaymentProviderTest extends BaseTestCase {

	private StripePaymentProvider $provider;
	private FakeStripeHttpClient $http_client;

	protected function setUp(): void {
		parent::setUp();

		$this->http_client = new FakeStripeHttpClient();
		ApiRequestor::setHttpClient( $this->http_client );
		$this->provider = $this->create_provider();
	}

	protected function tearDown(): void {
		ApiRequestor::setHttpClient( null );
		parent::tearDown();
	}

	public function test_get_slug(): void {
		$this->assertSame( 'stripe', StripePaymentProvider::get_slug() );
	}

	public function test_get_name(): void {
		$this->assertSame( 'Stripe', StripePaymentProvider::get_name() );
	}

	public function test_rest_webhook_returns_400_when_no_secret_configured(): void {
		delete_option( StripePaymentProvider::SETTING_WEBHOOK );

		$request = new WP_REST_Request( 'POST', '/kudos/v1/payment/webhook' );
		$request->set_body( '{}' );

		$response = $this->provider->rest_webhook( $request );

		$this->assertSame( 400, $response->get_status() );
	}

	public function test_create_payment_uses_payment_mode_for_one_off(): void {
		$this->http_client->set_response( 'checkout/sessions', $this->session_fixture() );

		$this->create_payment_fixture();

		$last = $this->http_client->get_last_request();
		$this->assertSame( Session::MODE_PAYMENT, $last['params']['mode'] );
	}

	public function test_create_payment_uses_subscription_mode_for_recurring(): void {
		$this->http_client->set_response( 'checkout/sessions', $this->session_fixture() );

		$this->create_payment_fixture( [ 'recurring' => 'true', 'recurring_frequency' => '1 month' ] );

		$last = $this->http_client->get_last_request();
		$this->assertSame( Session::MODE_SUBSCRIPTION, $last['params']['mode'] );
	}

	public function test_create_payment_includes_recurring_interval_in_price_data(): void {
		$this->http_client->set_response( 'checkout/sessions', $this->session_fixture() );

		$this->create_payment_fixture( [ 'recurring' => 'true', 'recurring_frequency' => '3 months' ] );

		$last      = $this->http_client->get_last_request();
		$recurring = $last['params']['line_items'][0]['price_data']['recurring'] ?? null;
		$this->assertSame( [ 'interval' => 'month', 'interval_count' => 3 ], $recurring );
	}

	public function test_create_payment_stores_session_id_on_transaction(): void {
		$this->http_client->set_response( 'checkout/sessions', $this->session_fixture( [ 'id' => 'cs_test_stored' ] ) );

		$result = $this->create_payment_fixture();

		$this->assertSame( 'cs_test_stored', $result['transaction']->vendor_payment_id );
	}

	public function test_create_payment_links_customer_if_provided(): void {
		$this->http_client->set_response( 'checkout/sessions', $this->session_fixture() );

		$this->create_payment_fixture( [], 'cus_test_abc123' );

		$last = $this->http_client->get_last_request();
		$this->assertSame( 'cus_test_abc123', $last['params']['customer'] );
	}

	public function test_create_payment_stores_customer_id_on_transaction(): void {
		$this->http_client->set_response( 'checkout/sessions', $this->session_fixture() );

		$result = $this->create_payment_fixture( [], 'cus_test_abc123' );

		$this->assertSame( 'cus_test_abc123', $result['transaction']->vendor_customer_id );
	}

	public function test_create_payment_returns_false_on_api_error(): void {
		$this->http_client->set_response( 'checkout/sessions', [ 'error' => [ 'type' => 'api_error', 'message' => 'fail' ] ], 500 );

		$result = $this->create_payment_fixture();

		$this->assertFalse( $result['checkout_url'] );
	}

	public function test_handle_status_change_marks_transaction_paid_on_complete_session(): void {
		/** @var TransactionRepository $transactions */
		$transactions   = $this->get_from_container( TransactionRepository::class );
		$transaction_id = $transactions->insert( new TransactionEntity( [ 'title' => 'Test' ] ) );

		$this->http_client->set_response(
			'checkout/sessions',
			$this->session_fixture(
				[
					'status'         => Session::STATUS_COMPLETE,
					'payment_status' => Session::PAYMENT_STATUS_PAID,
					'payment_intent' => 'pi_oneoff_abc123',
					'metadata'       => [ 'transaction_id' => (string) $transaction_id ],
				]
			)
		);

		$this->provider->handle_status_change( 'cs_test_abc123' );

		$updated = $transactions->get( $transaction_id );
		$this->assertSame( PaymentStatus::PAID, $updated->status );
		$this->assertSame( 'pi_oneoff_abc123', $updated->vendor_payment_id );
	}

	public function test_handle_status_change_marks_transaction_expired(): void {
		/** @var TransactionRepository $transactions */
		$transactions   = $this->get_from_container( TransactionRepository::class );
		$transaction_id = $transactions->insert( new TransactionEntity( [ 'title' => 'Test' ] ) );

		$this->http_client->set_response(
			'checkout/sessions',
			$this->session_fixture(
				[
					'status'   => Session::STATUS_EXPIRED,
					'metadata' => [ 'transaction_id' => (string) $transaction_id ],
				]
			)
		);

		$this->provider->handle_status_change( 'cs_test_abc123' );

		$updated = $transactions->get( $transaction_id );
		$this->assertSame( PaymentStatus::EXPIRED, $updated->status );
	}

	public function test_handle_status_change_creates_subscription_for_first_recurring_payment(): void {
		/** @var TransactionRepository $transactions */
		$transactions   = $this->get_from_container( TransactionRepository::class );
		$transaction_id = $transactions->insert(
			new TransactionEntity(
				[
					'title'         => 'Test',
					'sequence_type' => 'first',
				]
			)
		);

		$this->http_client->set_response(
			'checkout/sessions',
			$this->session_fixture(
				[
					'status'         => Session::STATUS_COMPLETE,
					'payment_status' => Session::PAYMENT_STATUS_PAID,
					'subscription'   => 'sub_test_abc123',
					'metadata'       => [
						'transaction_id'      => (string) $transaction_id,
						'recurring_frequency' => '1 month',
						'recurring_length'    => '1',
					],
				]
			)
		);
		$this->http_client->set_response( 'subscriptions', [ 'id' => 'sub_test_abc123', 'object' => 'subscription' ] );

		$this->provider->handle_status_change( 'cs_test_abc123' );

		/** @var SubscriptionRepository $subscriptions */
		$subscriptions = $this->get_from_container( SubscriptionRepository::class );
		$subscription  = $subscriptions->find_one_by( [ 'vendor_subscription_id' => 'sub_test_abc123' ] );

		$this->assertNotNull( $subscription );
		$this->assertSame( '1 month', $subscription->frequency );

		// A one-year fixed term should be capped on the vendor side, one year past session creation.
		$update = null;
		foreach ( $this->http_client->get_requests() as $request ) {
			if ( str_contains( $request['absUrl'], 'subscriptions/sub_test_abc123' ) ) {
				$update = $request;
			}
		}
		$this->assertNotNull( $update, 'Expected a Stripe subscription update to set the cancellation date.' );
		$this->assertArrayHasKey( 'cancel_at', $update['params'] );
		$this->assertSame( strtotime( '+1 year', 1700000000 ), $update['params']['cancel_at'] );
	}

	public function test_handle_status_change_does_not_cap_open_ended_subscription(): void {
		/** @var TransactionRepository $transactions */
		$transactions   = $this->get_from_container( TransactionRepository::class );
		$transaction_id = $transactions->insert(
			new TransactionEntity(
				[
					'title'         => 'Test',
					'sequence_type' => 'first',
				]
			)
		);

		$this->http_client->set_response(
			'checkout/sessions',
			$this->session_fixture(
				[
					'status'         => Session::STATUS_COMPLETE,
					'payment_status' => Session::PAYMENT_STATUS_PAID,
					'subscription'   => 'sub_test_abc123',
					'metadata'       => [
						'transaction_id'      => (string) $transaction_id,
						'recurring_frequency' => '1 month',
						'recurring_length'    => '0',
					],
				]
			)
		);

		$this->provider->handle_status_change( 'cs_test_abc123' );

		foreach ( $this->http_client->get_requests() as $request ) {
			$this->assertStringNotContainsString( 'subscriptions/sub_test_abc123', $request['absUrl'], 'Open-ended subscription should not be capped.' );
		}
	}

	public function test_handle_status_change_skips_already_processed_transaction(): void {
		/** @var TransactionRepository $transactions */
		$transactions   = $this->get_from_container( TransactionRepository::class );
		$transaction_id = $transactions->insert(
			new TransactionEntity(
				[
					'title'  => 'Test',
					'status' => PaymentStatus::PAID,
				]
			)
		);

		$this->http_client->set_response(
			'checkout/sessions',
			$this->session_fixture(
				[
					'status'         => Session::STATUS_COMPLETE,
					'payment_status' => Session::PAYMENT_STATUS_PAID,
					'metadata'       => [ 'transaction_id' => (string) $transaction_id ],
				]
			)
		);

		$this->provider->handle_status_change( 'cs_test_abc123' );

		// Status should remain unchanged.
		$updated = $transactions->get( $transaction_id );
		$this->assertSame( PaymentStatus::PAID, $updated->status );
	}

	public function test_handle_invoice_payment_skips_non_subscription_cycle_billing_reason(): void {
		/** @var TransactionRepository $transactions */
		$transactions = $this->get_from_container( TransactionRepository::class );
		$count_before = count( $transactions->all() );

		$this->http_client->set_response(
			'invoices',
			$this->invoice_fixture( [ 'billing_reason' => 'subscription_create' ] )
		);

		$this->provider->handle_status_change( 'in_test_abc123' );

		$this->assertSame( $count_before, count( $transactions->all() ) );
	}

	public function test_handle_invoice_payment_creates_recurring_transaction(): void {
		/** @var SubscriptionRepository $subscriptions */
		$subscriptions   = $this->get_from_container( SubscriptionRepository::class );
		$subscriptions->insert(
			$subscriptions->new_entity(
				[
					'vendor_subscription_id' => 'sub_test_abc123',
					'vendor'                 => 'stripe',
					'status'                 => 'active',
					'value'                  => 10.00,
					'currency'               => 'EUR',
				]
			)
		);

		$this->http_client->set_response( 'invoices', $this->invoice_fixture() );

		/** @var TransactionRepository $transactions */
		$transactions = $this->get_from_container( TransactionRepository::class );
		$count_before = count( $transactions->all() );

		$this->provider->handle_status_change( 'in_test_abc123' );

        /** @var TransactionEntity[] $all */
        $all   = $transactions->all();
		$this->assertCount( $count_before + 1, $all );

		$new = end( $all );
		$this->assertSame( PaymentStatus::PAID, $new->status );
		$this->assertSame( 'recurring', $new->sequence_type );
		$this->assertSame( 'EUR', $new->currency );
		$this->assertSame( 10.0, $new->value );
		// Stored (and de-duplicated) by the PaymentIntent, not the invoice id.
		$this->assertSame( 'pi_test_abc123', $new->vendor_payment_id );
	}

	public function test_handle_invoice_payment_skips_already_recorded_invoice(): void {
		/** @var SubscriptionRepository $subscriptions */
		$subscriptions = $this->get_from_container( SubscriptionRepository::class );
		$subscriptions->insert(
			$subscriptions->new_entity(
				[
					'vendor_subscription_id' => 'sub_test_abc123',
					'vendor'                 => 'stripe',
					'status'                 => 'active',
					'value'                  => 10.00,
					'currency'               => 'EUR',
				]
			)
		);

		// A transaction for this payment already exists, mimicking a webhook redelivery. It is keyed
		// by the PaymentIntent — the same id the redelivered invoice will resolve to.
		/** @var TransactionRepository $transactions */
		$transactions = $this->get_from_container( TransactionRepository::class );
		$transactions->insert(
			new TransactionEntity(
				[
					'title'             => 'Existing',
					'vendor_payment_id' => 'pi_test_abc123',
					'vendor'            => 'stripe',
					'status'            => PaymentStatus::PAID,
				]
			)
		);
		$count_before = count( $transactions->all() );

		$this->http_client->set_response( 'invoices', $this->invoice_fixture() );

		$this->provider->handle_status_change( 'in_test_abc123' );

		$this->assertSame( $count_before, count( $transactions->all() ), 'A redelivered invoice must not create a duplicate transaction.' );
	}

	public function test_cancel_subscription_returns_true_when_stripe_confirms_canceled(): void {
		$this->http_client->set_response( 'subscriptions', [ 'id' => 'sub_test_abc123', 'object' => 'subscription', 'status' => 'canceled' ] );

		$subscription                        = new SubscriptionEntity();
		$subscription->vendor_subscription_id = 'sub_test_abc123';

		$result = $this->provider->cancel_subscription( $subscription );

		$this->assertTrue( $result );
	}

	public function test_cancel_subscription_returns_false_when_no_vendor_subscription_id(): void {
		/** @var SubscriptionRepository $subscriptions */
		$subscriptions = $this->get_from_container( SubscriptionRepository::class );
		$subscription  = $subscriptions->new_entity( [ 'vendor_subscription_id' => '' ] );

		$result = $this->provider->cancel_subscription( $subscription );

		$this->assertFalse( $result );
	}

	public function test_handle_status_change_reconciles_subscription_first_payment_to_payment_intent(): void {
		/** @var TransactionRepository $transactions */
		$transactions   = $this->get_from_container( TransactionRepository::class );
		$transaction_id = $transactions->insert(
			new TransactionEntity(
				[
					'title'         => 'Test',
					'sequence_type' => 'first',
				]
			)
		);

		// A subscription session carries no session-level PaymentIntent; it lives on the first
		// invoice, expanded onto the session via `invoice.payments`.
		$this->http_client->set_response(
			'checkout/sessions',
			$this->session_fixture(
				[
					'status'         => Session::STATUS_COMPLETE,
					'payment_status' => Session::PAYMENT_STATUS_PAID,
					'subscription'   => 'sub_test_abc123',
					'payment_intent' => null,
					'invoice'        => [
						'id'       => 'in_sub_first',
						'object'   => 'invoice',
						'payments' => $this->payments_list( 'pi_sub_first' ),
					],
					'metadata'       => [
						'transaction_id'      => (string) $transaction_id,
						'recurring_frequency' => '1 month',
						'recurring_length'    => '0',
					],
				]
			)
		);

		$this->provider->handle_status_change( 'cs_test_abc123' );

		$updated = $transactions->get( $transaction_id );
		$this->assertSame( 'pi_sub_first', $updated->vendor_payment_id );
	}

	public function test_refund_creates_refund_against_stored_payment_intent(): void {
		/** @var TransactionRepository $transactions */
		$transactions   = $this->get_from_container( TransactionRepository::class );
		$transaction_id = $transactions->insert(
			new TransactionEntity(
				[
					'title'             => 'Test',
					'vendor'            => 'stripe',
					'vendor_payment_id' => 'pi_test_abc123',
					'status'            => PaymentStatus::PAID,
				]
			)
		);

		$this->http_client->set_response( 'refunds', [ 'id' => 're_test_abc123', 'object' => 'refund', 'status' => 'succeeded' ] );

		$result = $this->provider->refund( $transaction_id );

		$this->assertTrue( $result );
		$last = $this->http_client->get_last_request();
		$this->assertStringContainsString( '/refunds', $last['absUrl'] );
		$this->assertSame( 'pi_test_abc123', $last['params']['payment_intent'] );
	}

	public function test_refund_returns_false_without_vendor_payment_id(): void {
		/** @var TransactionRepository $transactions */
		$transactions   = $this->get_from_container( TransactionRepository::class );
		$transaction_id = $transactions->insert( new TransactionEntity( [ 'title' => 'Test', 'vendor' => 'stripe' ] ) );

		$this->assertFalse( $this->provider->refund( $transaction_id ) );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	private function create_provider(): StripePaymentProvider {
		$provider = new StripePaymentProvider(
			$this->get_from_container( TransactionRepository::class ),
			$this->get_from_container( SubscriptionRepository::class )
		);
		$provider->setLogger( $this->createMock( LoggerInterface::class ) );

		// Inject a real StripeClient wired to our fake HTTP client.
		$ref = new ReflectionProperty( StripePaymentProvider::class, 'stripe' );
		$ref->setAccessible( true );
		$ref->setValue( $provider, new StripeClient( 'sk_test_fakekeyfortesting' ) );

		return $provider;
	}

	/**
	 * Creates a payment with default args, returning the result array.
	 *
	 * @param array   $overrides         Payment args to override.
	 * @param ?string $vendor_customer_id Optional Stripe customer ID.
	 */
	private function create_payment_fixture( array $overrides = [], ?string $vendor_customer_id = null ): array {
		/** @var TransactionRepository $transactions */
		$transactions = $this->get_from_container( TransactionRepository::class );

		$campaign    = new CampaignEntity();
		$campaign_id = $this->get_from_container( CampaignRepository::class )->insert( $campaign );

		$transaction    = new TransactionEntity( [ 'title' => 'Test' ] );
		$transaction_id = $transactions->insert( $transaction );
		$transaction    = $transactions->get( $transaction_id );

		$args = array_merge(
			[
				'value'               => 10,
				'currency'            => 'EUR',
				'recurring'           => 'false',
				'recurring_frequency' => '',
				'recurring_length'    => 0,
				'return_url'          => 'https://example.com',
				'campaign_id'         => $campaign_id,
				'email'               => 'donor@example.com',
				'name'                => 'Jane Donor',
			],
			$overrides
		);

		$checkout_url = $this->provider->create_payment( $args, $transaction, $vendor_customer_id );

		return [
			'transaction_id' => $transaction_id,
			'transaction'    => $transactions->get( $transaction_id ),
			'checkout_url'   => $checkout_url,
		];
	}

	/**
	 * Returns a minimal Checkout Session response array, mergeable with overrides.
	 *
	 * @param array $overrides Fields to override.
	 */
	private function session_fixture( array $overrides = [] ): array {
		return array_merge(
			[
				'id'             => 'cs_test_abc123',
				'object'         => 'checkout.session',
				'created'        => 1700000000,
				'url'            => 'https://checkout.stripe.com/pay/cs_test_abc123',
				'status'         => 'open',
				'payment_status' => 'unpaid',
				'payment_intent' => null,
				'subscription'   => null,
				'livemode'       => false,
				'mode'           => 'payment',
				'metadata'       => [],
			],
			$overrides
		);
	}

	/**
	 * Returns a minimal Invoice response array for a subscription cycle, mergeable with overrides.
	 *
	 * @param array $overrides Fields to override.
	 */
	private function invoice_fixture( array $overrides = [] ): array {
		return array_merge(
			[
				'id'             => 'in_test_abc123',
				'object'         => 'invoice',
				'billing_reason' => 'subscription_cycle',
				'currency'       => 'eur',
				'amount_paid'    => 1000,
				'livemode'       => false,
				'customer'       => 'cus_test_abc123',
				'payments'       => $this->payments_list(),
				'parent'         => [
					'type'                 => 'subscription_details',
					'subscription_details' => [
						'subscription' => 'sub_test_abc123',
					],
				],
			],
			$overrides
		);
	}

	/**
	 * Returns an expanded invoice `payments` list object exposing a PaymentIntent, as the SDK
	 * would deserialize it from a retrieve with `expand: ['payments']`.
	 *
	 * @param string $payment_intent The PaymentIntent id the payment resolves to.
	 */
	private function payments_list( string $payment_intent = 'pi_test_abc123' ): array {
		return [
			'object'   => 'list',
			'has_more' => false,
			'data'     => [
				[
					'id'      => 'inpay_test_abc123',
					'object'  => 'invoice_payment',
					'status'  => 'paid',
					'payment' => [
						'type'           => 'payment_intent',
						'payment_intent' => $payment_intent,
					],
				],
			],
		];
	}
}