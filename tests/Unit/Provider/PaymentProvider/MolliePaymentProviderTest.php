<?php
namespace IseardMedia\Kudos\Tests\Provider\PaymentProvider;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Tests\BaseTestCase;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Fake\MockMollieClient;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Fake\MockResponse;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Http\PendingRequest;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Http\Requests\CreateCustomerRequest;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Http\Requests\CreatePaymentRequest;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\MollieApiClient;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Resources\Customer;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Resources\Payment;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Types\SequenceType;
use IseardMedia\Kudos\Provider\PaymentProvider\MolliePaymentProvider;
use Psr\Log\LoggerInterface;

/**
 * @covers \IseardMedia\Kudos\Provider\PaymentProvider\MolliePaymentProvider;
 */
class MolliePaymentProviderTest extends BaseTestCase {

	private MockMollieClient $client;
	private MolliePaymentProvider $vendor;

	protected function setUp(): void {
		parent::setUp();

		$this->client = MollieApiClient::fake([
			CreatePaymentRequest::class => MockResponse::resource(Payment::class)->with([
				'id' => 'tr_abc123',
				'_links' => [
					'checkout' => [
						'href' => 'https://mollie.com/checkout/123',
						'type' => 'text/html',
					],
				],
			])->create(),
		], true);

		$this->vendor = $this->create_vendor($this->client);
	}

	/**
	 * Test that create_payment updates a transaction.
	 */
	public function test_creates_payment_successfully(): void {
		$result = $this->create_payment_fixture();

		$this->assertSame('https://mollie.com/checkout/123', $result['checkout_url']);
		$this->assertSame('tr_abc123', $result['transaction']->vendor_payment_id);
	}

	/**
	 * Test that create_payment updates the return url.
	 */
	public function test_creates_payment_update_redirect_url(): void {
		$this->create_payment_fixture(['show_return_message' => true]);

		$this->client->assertSent(function (PendingRequest $request) {
			return $request->payload()->has('redirectUrl')
				&& strpos($request->payload()->get('redirectUrl'), 'kudos_transaction_id=') !== false;
		});
	}

	/**
	 * Test that create_payment does not update the return url.
	 */
	public function test_creates_payment_not_update_redirect_url(): void {
		$this->create_payment_fixture(['show_return_message' => false]);

		$this->client->assertSent(function (PendingRequest $request) {
			return $request->payload()->has('redirectUrl')
				&& strpos($request->payload()->get('redirectUrl'), 'kudos_transaction_id=') === false;
		});
	}

	/**
	 * Test that sequence type is set to first for recurring payments.
	 */
	public function test_creates_payment_sets_sequence_type_to_first_for_recurring(): void {
		$this->create_payment_fixture([
			'recurring' => 'true',
			'recurring_frequency' => '1 month',
		]);

		$this->client->assertSent(function (PendingRequest $request) {
			return $request->payload()->get('sequenceType') === SequenceType::FIRST;
		});
	}

	/**
	 * Test that non-recurring payments have a sequence type of oneoff.
	 */
	public function test_creates_payment_sets_sequence_type_to_oneoff_for_non_recurring(): void {
		$this->create_payment_fixture([
			'recurring' => 'false',
		]);

		$this->client->assertSent(function (PendingRequest $request) {
			return $request->payload()->get('sequenceType') === SequenceType::ONEOFF;
		});
	}

	/**
	 * Test that the Mollie payment metadata is created correctly.
	 */
	public function test_creates_payment_includes_expected_metadata(): void {
		$this->create_payment_fixture();

		$this->client->assertSent(function (PendingRequest $request) {
			$meta = $request->payload()->get('metadata');
			return $meta['email'] === 'john.smith@example.com'
				&& $meta['name'] === 'John Smith'
				&& isset($meta['campaign_id'])
				&& isset($meta['transaction_id']);
		});
	}

	/**
	 * Test that customer id is included in payment request.
	 */
	public function test_creates_payment_with_customer_id(): void {
		$this->create_payment_fixture([], 'cst_abc123');

		$this->client->assertSent(function (PendingRequest $request) {
			return $request->payload()->get('customerId') === 'cst_abc123';
		});
	}

	/**
	 * Test that create_payment returns false if exception thrown by Mollie api.
	 */
	public function test_create_payment_returns_false_on_failure(): void {
		$client = MollieApiClient::fake([
			CreatePaymentRequest::class => MockResponse::unprocessableEntity(),
		]);
		$vendor = $this->create_vendor($client);

		$campaign = new CampaignEntity(['title' => 'Fail campaign']);
		$campaign_id = $this->get_from_container(CampaignRepository::class)->insert($campaign);
		$payment_args = [
			'amount' => [ 'currency' => 'EUR', 'value' => '10.00' ],
			'description' => 'Fail test',
			'recurring' => 'false',
			'recurring_length' => '0',
			'value' => 10,
			'return_url' => 'https://example.com',
			'campaign_id' => $campaign_id,
			'currency' => 'EUR',
			'email' => 'fail@example.com',
			'name' => 'Fail Tester',
		];
		/** @var TransactionRepository $transactions */
		$transactions = $this->get_from_container(TransactionRepository::class);

		$transaction = new TransactionEntity(['title' => 'Failing Transaction']);
		$transaction_id = $transactions->insert($transaction);
		$transaction = $transactions->get($transaction_id);

		$result = $vendor->create_payment($payment_args, $transaction);

		$this->assertFalse($result);
	}

	/**
	 * Test that valid customer is created.
	 */
	public function test_creates_customer(): void {
		$client = MollieApiClient::fake([
			CreateCustomerRequest::class => MockResponse::resource(Customer::class)->create(),
		]);
		$vendor = $this->create_vendor($client);

		$customer = $vendor->create_customer('Jane Doe', 'jane@example.com');

		$this->assertInstanceOf(Customer::class, $customer);
		$client->assertSent(function (PendingRequest $request) {
			return get_class($request->getRequest()) === CreateCustomerRequest::class;
		});
	}

	/**
	 * Check the response from get_slug is valid.
	 */
	public function test_get_vendor_slug(): void {
		$this->assertSame('mollie', $this->vendor->get_slug());
	}

	/**
	 * Creates a MolliePaymentProvider with the given API client.
	 */
	private function create_vendor(MockMollieClient $client): MolliePaymentProvider {
		$logger = $this->createMock(LoggerInterface::class);
		$vendor = new MolliePaymentProvider(
			$client,
			$this->get_from_container(CampaignRepository::class),
			$this->get_from_container(TransactionRepository::class),
			$this->get_from_container(DonorRepository::class),
			$this->get_from_container(SubscriptionRepository::class)
		);
		$vendor->setLogger($logger);

		return $vendor;
	}

	/**
	 * Creates a payment with default values that can be overridden.
	 *
	 * @param array $overrides Values to override.
	 * @param ?string $vendor_customer_id The vendor customer id.
	 */
	private function create_payment_fixture(array $overrides = [], ?string $vendor_customer_id = null): array {
		$default_args = [
			'amount' => [ 'currency' => 'EUR', 'value' => '10.00' ],
			'description' => 'Test donation',
			'recurring' => 'false',
			'recurring_length' => '0',
			'value' => 10,
			'return_url' => 'https://example.com',
			'campaign_id' => null,
			'currency' => 'EUR',
			'email' => 'john.smith@example.com',
			'name' => 'John Smith'
		];
		/** @var TransactionRepository $transactions */
		$transactions = $this->get_from_container(TransactionRepository::class);

		$payment_args = array_merge($default_args, $overrides);

		$campaign = new CampaignEntity(['show_return_message' => $payment_args['show_return_message'] ?? false]);
		$campaign_id = $this->get_from_container(CampaignRepository::class)->insert($campaign);

		$payment_args['campaign_id'] = $campaign_id;

		$transaction = new TransactionEntity(['title' => 'Test transaction']);
		$transaction_id = $transactions->insert($transaction);
		$transaction = $transactions->get($transaction_id);

		$checkout_url = $this->vendor->create_payment($payment_args, $transaction, $vendor_customer_id);

		$updated = $transactions->get($transaction_id);

		return [
			'transaction_id' => $transaction_id,
			'transaction'    => $updated,
			'checkout_url'   => $checkout_url,
		];
	}
}
