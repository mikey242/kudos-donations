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
use IseardMedia\Kudos\Service\EncryptionService;
use IseardMedia\Kudos\Tests\BaseTestCase;
use IseardMedia\Kudos\Tests\Stubs\FakeStripeHttpClient;
use IseardMedia\Kudos\ThirdParty\Stripe\ApiRequestor;
use IseardMedia\Kudos\ThirdParty\Stripe\Checkout\Session;
use IseardMedia\Kudos\ThirdParty\Stripe\StripeClient;
use Psr\Log\LoggerInterface;
use ReflectionMethod;
use ReflectionProperty;
use WP_REST_Request;

/**
 * @covers \IseardMedia\Kudos\Provider\PaymentProvider\StripePaymentProvider
 */
class StripePaymentProviderTest extends BaseTestCase {

	private const TEST_KEY    = 'sk_test_fakekeyfortesting';
	private const LIVE_KEY    = 'sk_live_fakekeyfortesting';
	private const TEST_SECRET = 'whsec_test_fakesecret';
	private const LIVE_SECRET = 'whsec_live_fakesecret';

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

	public function test_saving_key_activates_mode_when_current_mode_has_no_key(): void {
		// Fresh setup: default test mode with no keys. Saving a live key should activate live mode.
		update_option( StripePaymentProvider::SETTING_API_MODE, 'test' );

		$this->provider->handle_key_update( 'sk_live_examplekey', '', StripePaymentProvider::SETTING_API_KEY_LIVE );

		$this->assertSame( 'live', get_option( StripePaymentProvider::SETTING_API_MODE ) );
	}

	public function test_saving_key_does_not_demote_a_configured_mode(): void {
		// Live site with a live key already stored: re-saving a test key must leave it in live mode.
		update_option( StripePaymentProvider::SETTING_API_MODE, 'live' );
		update_option( StripePaymentProvider::SETTING_API_KEY_ENCRYPTED_LIVE, 'existing-encrypted-live-key' );

		$this->provider->handle_key_update( 'sk_test_examplekey', '', StripePaymentProvider::SETTING_API_KEY_TEST );

		$this->assertSame( 'live', get_option( StripePaymentProvider::SETTING_API_MODE ) );
	}

	// -------------------------------------------------------------------------
	// Mode handling: events and existing objects use their own mode, not the site's
	// -------------------------------------------------------------------------

	public function test_rest_webhook_verifies_live_event_with_live_secret_while_in_test_mode(): void {
		$this->configure_webhooks( 'test' );

		$response = $this->provider->rest_webhook( $this->signed_webhook_request( true, self::LIVE_SECRET ) );

		$this->assertSame( 200, $response->get_status() );
		$this->assertNotFalse(
			as_next_scheduled_action(
				'kudos_stripe_handle_status_change',
				[
					'payment_id' => 'cs_abc123',
					'mode'       => 'live',
				],
				'kudos-donations'
			),
			'The status change must be queued with the event\'s mode.'
		);
	}

	public function test_rest_webhook_verifies_test_event_with_test_secret_while_in_live_mode(): void {
		$this->configure_webhooks( 'live' );

		$response = $this->provider->rest_webhook( $this->signed_webhook_request( false, self::TEST_SECRET ) );

		$this->assertSame( 200, $response->get_status() );
		$this->assertNotFalse(
			as_next_scheduled_action(
				'kudos_stripe_handle_status_change',
				[
					'payment_id' => 'cs_abc123',
					'mode'       => 'test',
				],
				'kudos-donations'
			)
		);
	}

	public function test_rest_webhook_rejects_event_whose_livemode_does_not_match_its_signature(): void {
		$this->configure_webhooks( 'test' );

		// Claims to be live but is signed with the test secret, so it is checked against the live one.
		$response = $this->provider->rest_webhook( $this->signed_webhook_request( true, self::TEST_SECRET ) );

		$this->assertSame( 400, $response->get_status() );
	}

	public function test_handle_status_change_fetches_with_the_given_mode(): void {
		update_option( StripePaymentProvider::SETTING_API_MODE, 'test' );
		$this->http_client->set_response( 'invoices', $this->invoice_fixture( [ 'billing_reason' => 'subscription_create' ] ) );

		$this->provider->handle_status_change( 'in_live_abc123', 'live' );

		$this->assertSame( self::LIVE_KEY, $this->last_request_key() );
	}

	public function test_handle_status_change_without_mode_uses_current_mode(): void {
		// Actions queued before 4.3.0 carry no mode.
		update_option( StripePaymentProvider::SETTING_API_MODE, 'live' );
		$this->http_client->set_response( 'invoices', $this->invoice_fixture( [ 'billing_reason' => 'subscription_create' ] ) );

		$this->provider->handle_status_change( 'in_live_abc123' );

		$this->assertSame( self::LIVE_KEY, $this->last_request_key() );
	}

	public function test_refund_uses_the_transaction_mode(): void {
		update_option( StripePaymentProvider::SETTING_API_MODE, 'test' );
		/** @var TransactionRepository $transactions */
		$transactions   = $this->get_from_container( TransactionRepository::class );
		$transaction_id = $transactions->insert(
			new TransactionEntity(
				[
					'title'             => 'Live payment',
					'vendor'            => 'stripe',
					'vendor_payment_id' => 'pi_live_abc123',
					'status'            => PaymentStatus::PAID,
					'mode'              => 'live',
				]
			)
		);
		$this->http_client->set_response( 'refunds', [ 'id' => 're_live_abc123', 'object' => 'refund', 'status' => 'succeeded' ] );

		$this->assertTrue( $this->provider->refund( $transaction_id ) );
		$this->assertSame( self::LIVE_KEY, $this->last_request_key() );
	}

	public function test_refund_without_mode_uses_current_mode(): void {
		update_option( StripePaymentProvider::SETTING_API_MODE, 'test' );
		/** @var TransactionRepository $transactions */
		$transactions   = $this->get_from_container( TransactionRepository::class );
		$transaction_id = $transactions->insert(
			new TransactionEntity(
				[
					'title'             => 'No mode',
					'vendor'            => 'stripe',
					'vendor_payment_id' => 'pi_test_abc123',
					'status'            => PaymentStatus::PAID,
				]
			)
		);
		$this->http_client->set_response( 'refunds', [ 'id' => 're_test_abc123', 'object' => 'refund', 'status' => 'succeeded' ] );

		$this->assertTrue( $this->provider->refund( $transaction_id ) );
		$this->assertSame( self::TEST_KEY, $this->last_request_key() );
	}

	public function test_cancel_subscription_uses_the_first_transaction_mode(): void {
		update_option( StripePaymentProvider::SETTING_API_MODE, 'test' );
		/** @var TransactionRepository $transactions */
		$transactions   = $this->get_from_container( TransactionRepository::class );
		$transaction_id = $transactions->insert(
			new TransactionEntity(
				[
					'title'  => 'First live payment',
					'vendor' => 'stripe',
					'mode'   => 'live',
				]
			)
		);
		$this->http_client->set_response( 'subscriptions', [ 'id' => 'sub_live_abc123', 'object' => 'subscription', 'status' => 'canceled' ] );

		$subscription                         = new SubscriptionEntity();
		$subscription->vendor_subscription_id = 'sub_live_abc123';
		$subscription->transaction_id         = $transaction_id;

		$this->assertTrue( $this->provider->cancel_subscription( $subscription ) );
		$this->assertSame( self::LIVE_KEY, $this->last_request_key() );
	}

	public function test_delete_endpoints_for_url_removes_only_matching_endpoints(): void {
		$webhook_url = get_rest_url( null, 'kudos/v1/payment/webhook/stripe' );

		// The delete path (webhook_endpoints/{id}) must be matched before the bare list path.
		$this->http_client->set_response( 'webhook_endpoints/', [ 'id' => 'we_match', 'object' => 'webhook_endpoint', 'deleted' => true ] );
		$this->http_client->set_response(
			'webhook_endpoints',
			[
				'object'   => 'list',
				'has_more' => false,
				'data'     => [
					[ 'id' => 'we_match', 'object' => 'webhook_endpoint', 'url' => $webhook_url ],
					[ 'id' => 'we_other', 'object' => 'webhook_endpoint', 'url' => 'https://example.com/other' ],
				],
			]
		);

		$method = new ReflectionMethod( StripePaymentProvider::class, 'delete_endpoints_for_url' );
		$method->setAccessible( true );
		$method->invoke( $this->provider, new StripeClient( self::TEST_KEY ), $webhook_url );

		$requests = $this->http_client->get_requests();
		$deleted  = array_filter( $requests, static fn( $r ) => str_contains( $r['absUrl'], 'webhook_endpoints/we_match' ) );
		$this->assertCount( 1, $deleted, 'The endpoint matching our URL should be deleted.' );
		$this->assertEmpty(
			array_filter( $requests, static fn( $r ) => str_contains( $r['absUrl'], 'we_other' ) ),
			'Endpoints for other URLs must not be touched.'
		);
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
		$provider->set_encryption( $this->get_from_container( EncryptionService::class ) );

		// Inject a real StripeClient per mode, wired to our fake HTTP client. Distinct keys let tests
		// assert which mode's client made a request.
		$ref = new ReflectionProperty( StripePaymentProvider::class, 'clients' );
		$ref->setAccessible( true );
		$ref->setValue(
			$provider,
			[
				'test' => new StripeClient( self::TEST_KEY ),
				'live' => new StripeClient( self::LIVE_KEY ),
			]
		);

		return $provider;
	}

	/**
	 * Returns the API key the most recent request was authenticated with.
	 */
	private function last_request_key(): string {
		foreach ( $this->http_client->get_last_request()['headers'] ?? [] as $header ) {
			if ( str_starts_with( $header, 'Authorization: Bearer ' ) ) {
				return substr( $header, \strlen( 'Authorization: Bearer ' ) );
			}
		}
		return '';
	}

	/**
	 * Builds a webhook request carrying a signed Stripe event.
	 *
	 * @param bool   $livemode The event's livemode flag.
	 * @param string $secret   The secret to sign the payload with.
	 */
	private function signed_webhook_request( bool $livemode, string $secret ): WP_REST_Request {
		$payload   = (string) wp_json_encode(
			[
				'id'       => 'evt_abc123',
				'object'   => 'event',
				'type'     => 'checkout.session.completed',
				'livemode' => $livemode,
				'data'     => [
					'object' => [
						'id'     => 'cs_abc123',
						'object' => 'checkout.session',
					],
				],
			]
		);
		$timestamp = time();
		$signature = hash_hmac( 'sha256', "{$timestamp}.{$payload}", $secret );

		$request = new WP_REST_Request( 'POST', '/kudos/v1/payment/webhook/stripe' );
		$request->set_body( $payload );
		$request->set_header( 'stripe_signature', "t={$timestamp},v1={$signature}" );

		return $request;
	}

	/**
	 * Stores webhook secrets for both modes and sets the site's current mode.
	 *
	 * @param string $current_mode The site's current API mode.
	 */
	private function configure_webhooks( string $current_mode ): void {
		update_option( StripePaymentProvider::SETTING_API_MODE, $current_mode );
		update_option(
			StripePaymentProvider::SETTING_WEBHOOK,
			[
				'test' => [ 'secret' => self::TEST_SECRET ],
				'live' => [ 'secret' => self::LIVE_SECRET ],
			]
		);
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