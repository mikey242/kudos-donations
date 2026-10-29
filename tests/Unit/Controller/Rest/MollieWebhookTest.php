<?php
namespace IseardMedia\Kudos\Tests\Controller\Rest;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Provider\PaymentProvider\MolliePaymentProvider;
use IseardMedia\Kudos\Service\EncryptionService;
use IseardMedia\Kudos\Tests\BaseTestCase;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\EndpointCollection\PaymentEndpointCollection;
use IseardMedia\Kudos\Tests\Stubs\TestableMollieApiClient;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Resources\Payment;

use Psr\Log\LoggerInterface;
use WP_REST_Request;

/**
 * @covers \IseardMedia\Kudos\Provider\PaymentProvider\MolliePaymentProvider::rest_webhook
 * @covers \IseardMedia\Kudos\Provider\PaymentProvider\MolliePaymentProvider::handle_status_change
 */
class MollieWebhookTest extends BaseTestCase {

	private MolliePaymentProvider $provider;
	private TestableMollieApiClient $api_mock;
    private TransactionRepository $transaction_repository;
	private CampaignRepository $campaign_repository;

	protected function setUp(): void {
		parent::setUp();

		$this->api_mock = $this->createMock(TestableMollieApiClient::class);
		$this->transaction_repository = $this->get_from_container(TransactionRepository::class);
		$this->campaign_repository = $this->get_from_container(CampaignRepository::class);
        $logger = $this->createMock(LoggerInterface::class);

		$donor_repository = $this->get_from_container(DonorRepository::class);
		$subscription_repository = $this->get_from_container(SubscriptionRepository::class);

		$this->provider = new MolliePaymentProvider(
			$this->api_mock,
			$this->campaign_repository,
			$this->transaction_repository,
			$donor_repository,
			$subscription_repository
		);
		$this->provider->setLogger($logger);
		// The lazy client reads (and would decrypt) the stored key on first use, so wire
		// encryption just as the container does. No key is stored, so the mock client is used as-is.
		$this->provider->set_encryption($this->get_from_container(EncryptionService::class));
	}

	/**
	 * Test successful webhook request returns 200 OK response.
	 */
	public function test_webhook_returns_success_response(): void {
		$request = new WP_REST_Request('POST', '/kudos/v1/payment/webhook');
		$request->set_param('id', 'tr_test123');

		$response = $this->provider->rest_webhook($request);

		$this->assertTrue($response->data['success']);
		$this->assertEquals('tr_test123', $response->data['id']);
		$this->assertArrayHasKey('_links', $response->data);
	}

	/**
	 * Test webhook with missing payment ID parameter.
	 */
	public function test_webhook_handles_missing_payment_id(): void {
		$request = new WP_REST_Request('POST', '/kudos/v1/payment/webhook');

		$response = $this->provider->rest_webhook($request);

		$this->assertTrue($response->data['success']);
		$this->assertNull($response->data['id']);
	}

	/**
	 * Test webhook enqueues async action for processing.
	 */
	public function test_webhook_enqueues_async_action(): void {
		$request = new WP_REST_Request('POST', '/kudos/v1/payment/webhook');
		$request->set_param('id', 'tr_async123');

		$response = $this->provider->rest_webhook($request);

		$this->assertTrue($response->data['success']);
	}

	/**
	 * Test handle_status_change with paid payment updates transaction.
	 */
	public function test_handle_status_change_paid_payment(): void {
		// Create test campaign and transaction
		$campaign = new CampaignEntity(['title' => 'Test Campaign']);
		$campaign_id = $this->campaign_repository->insert($campaign);

		$transaction = new TransactionEntity([
			'title' => 'Test Transaction',
			'campaign_id' => $campaign_id,
			'status' => 'open'
		]);
		$transaction_id = $this->transaction_repository->insert($transaction);

		// Mock payment object
		$payment_mock = $this->createMock(Payment::class);
		$payment_mock->method('isPaid')->willReturn(true);
		$payment_mock->method('hasRefunds')->willReturn(false);
		$payment_mock->method('hasChargebacks')->willReturn(false);
		$payment_mock->method('hasSequenceTypeFirst')->willReturn(false);
		$payment_mock->method('hasSequenceTypeRecurring')->willReturn(false);

		$payment_mock->status = 'paid';
		$payment_mock->id = 'tr_test123';
		$payment_mock->customerId = 'cst_test123';
		$payment_mock->sequenceType = 'oneoff';
		$payment_mock->method = 'ideal';
		$payment_mock->mode = 'test';

		// Mock amount object
		$amount_mock = new \stdClass();
		$amount_mock->value = '10.00';
		$amount_mock->currency = 'EUR';
		$payment_mock->amount = $amount_mock;

		// Mock metadata object
		$metadata_mock = new \stdClass();
		$metadata_mock->transaction_id = $transaction_id;
		$payment_mock->metadata = $metadata_mock;

		// Mock payments endpoint
		$payments_mock = $this->createMock(PaymentEndpointCollection::class);
		$payments_mock->method('get')->willReturn($payment_mock);
		$this->api_mock->payments = $payments_mock;

		$this->provider->handle_status_change('tr_test123');

		// Verify transaction was updated
		$updated_transaction = $this->transaction_repository->get($transaction_id);
		$this->assertEquals('paid', $updated_transaction->status);
		$this->assertEquals('tr_test123', $updated_transaction->vendor_payment_id);
		$this->assertEquals(10.0, $updated_transaction->value);
		$this->assertEquals('EUR', $updated_transaction->currency);
	}

	/**
	 * Test handle_status_change with refunded payment. A refund always arrives for a transaction
	 * that is already paid, which must not be mistaken for a duplicate webhook.
	 */
	public function test_handle_status_change_refunded_payment(): void {
		// Create test transaction
		$campaign = new CampaignEntity(['title' => 'Test Campaign']);
		$campaign_id = $this->campaign_repository->insert($campaign);

		$transaction = new TransactionEntity([
			'title' => 'Test Transaction',
			'campaign_id' => $campaign_id,
			'status' => 'paid'
		]);
		$transaction_id = $this->transaction_repository->insert($transaction);

		$this->mock_refunded_payment('tr_test123', 5.00, 5.00, $transaction_id);

		$this->provider->handle_status_change('tr_test123');

		// Verify transaction refunds field was updated
		$updated_transaction = $this->transaction_repository->get($transaction_id);
		$this->assertEquals('paid', $updated_transaction->status);
		$this->assertEquals(['refunded' => 5.0, 'remaining' => 5.0], json_decode((string) $updated_transaction->refunds, true));
        // Verify that the refund action was triggered
		$this->assertSame(1, did_action('kudos_transaction_refunded'));
		// The payment was already paid, so the paid hook (and its receipt) must not fire again.
		$this->assertSame(0, did_action('kudos_transaction_paid'));
	}

	/**
	 * A refunded recurring payment is matched by its own payment id, never by the subscription
	 * metadata, whose transaction_id belongs to the first payment.
	 */
	public function test_handle_status_change_refunded_recurring_payment(): void {
		$first_id     = $this->transaction_repository->insert(new TransactionEntity(['title' => 'First', 'status' => 'paid', 'vendor_payment_id' => 'tr_first']));
		$recurring_id = $this->transaction_repository->insert(new TransactionEntity(['title' => 'Recurring', 'status' => 'paid', 'vendor_payment_id' => 'tr_recurring']));

		$this->mock_refunded_payment('tr_recurring', 10.00, 0.00, $first_id, true);

		$this->provider->handle_status_change('tr_recurring');

		$this->assertNotEmpty($this->transaction_repository->get($recurring_id)->refunds);
		$this->assertEmpty($this->transaction_repository->get($first_id)->refunds);
	}

	/**
	 * A redelivered refund webhook must not record or announce the refund twice.
	 */
	public function test_handle_status_change_refund_is_recorded_once(): void {
		$transaction_id = $this->transaction_repository->insert(new TransactionEntity(['title' => 'Test', 'status' => 'paid', 'vendor_payment_id' => 'tr_test123']));

		$this->mock_refunded_payment('tr_test123', 5.00, 5.00, $transaction_id);

		$this->provider->handle_status_change('tr_test123');
		$this->provider->handle_status_change('tr_test123');

		$this->assertSame(1, did_action('kudos_transaction_refunded'));
	}

	/**
	 * Configures the mocked Mollie client to return a refunded payment.
	 *
	 * @param string $payment_id     The Mollie payment id.
	 * @param float  $refunded       The amount refunded.
	 * @param float  $remaining      The amount remaining.
	 * @param int    $transaction_id The transaction id in the payment metadata.
	 * @param bool   $recurring      Whether the payment is a recurring one.
	 */
	private function mock_refunded_payment(string $payment_id, float $refunded, float $remaining, int $transaction_id, bool $recurring = false): void {
		$payment_mock = $this->createMock(Payment::class);
		$payment_mock->method('isPaid')->willReturn(true);
		$payment_mock->method('hasRefunds')->willReturn(true);
		$payment_mock->method('hasChargebacks')->willReturn(false);
		$payment_mock->method('hasSequenceTypeRecurring')->willReturn($recurring);
		$payment_mock->method('getAmountRefunded')->willReturn($refunded);
		$payment_mock->method('getAmountRemaining')->willReturn($remaining);

		$payment_mock->id     = $payment_id;
		$payment_mock->status = 'paid';

		$metadata_mock                 = new \stdClass();
		$metadata_mock->transaction_id = $transaction_id;
		$payment_mock->metadata        = $metadata_mock;

		$payments_mock = $this->createMock(PaymentEndpointCollection::class);
		$payments_mock->method('get')->willReturn($payment_mock);
		$this->api_mock->payments = $payments_mock;
	}

	/**
	 * Test handle_status_change with unknown transaction ID logs warning.
	 */
	public function test_handle_status_change_unknown_transaction(): void {
		// Mock payment with non-existent transaction ID
		$payment_mock = $this->createMock(Payment::class);
		
		$metadata_mock = new \stdClass();
		$metadata_mock->transaction_id = 99999; // Non-existent transaction
		$payment_mock->metadata = $metadata_mock;

		$payments_mock = $this->createMock(PaymentEndpointCollection::class);
		$payments_mock->method('get')->willReturn($payment_mock);
		$this->api_mock->payments = $payments_mock;

		// Expect warning to be logged - using a specific mock method call
		$logger_mock = $this->createMock(LoggerInterface::class);
		$logger_mock->expects($this->once())
		            ->method('warning')
		            ->with($this->stringContains('Webhook received for unknown transaction'));
		$this->provider->setLogger($logger_mock);

		$this->provider->handle_status_change('tr_test123');
	}

	/**
	 * Test handle_status_change skips already processed transactions.
	 */
	public function test_handle_status_change_skips_processed_transaction(): void {
		// Create test transaction with non-open status.
		$campaign = new CampaignEntity(['title' => 'Test Campaign']);
		$campaign_id = $this->campaign_repository->insert($campaign);

		$transaction = new TransactionEntity([
			'title' => 'Test Transaction',
			'campaign_id' => $campaign_id,
			'status' => 'paid' // Already processed
		]);
		$transaction_id = $this->transaction_repository->insert($transaction);

		// Mock payment object
		$payment_mock = $this->createMock(Payment::class);
		
		$metadata_mock = new \stdClass();
		$metadata_mock->transaction_id = $transaction_id;
		$payment_mock->metadata = $metadata_mock;

		$payments_mock = $this->createMock(PaymentEndpointCollection::class);
		$payments_mock->method('get')->willReturn($payment_mock);
		$this->api_mock->payments = $payments_mock;

		// Expect debug log about skipping (the specific call we care about)
		$logger_mock = $this->createMock(LoggerInterface::class);
		$logger_mock->expects($this->atLeastOnce())
		            ->method('debug')
		            ->with($this->logicalOr(
		                $this->stringContains('Payment retrieved from Mollie'),
		                $this->stringContains('Duplicate handle_status_change call')
		            ));
		$this->provider->setLogger($logger_mock);

		$this->provider->handle_status_change('tr_test123');
	}
}