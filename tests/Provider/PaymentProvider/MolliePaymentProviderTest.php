<?php
namespace IseardMedia\Kudos\Tests\Provider\PaymentProvider;

use IseardMedia\Kudos\Domain\Entity\CampaignEntity;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\CampaignRepository;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Tests\BaseTestCase;
use IseardMedia\Kudos\ThirdParty\Mollie\Api\Exceptions\RequestException;
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

	private MolliePaymentProvider $vendor;
	private MollieApiClient $api_mock;

	protected function setUp(): void {
		parent::setUp();

		$this->api_mock = $this->createMock(MollieApiClient::class);
		$logger = $this->createMock(LoggerInterface::class);
		$transactions = $this->get_from_container(TransactionRepository::class);
		$donors = $this->get_from_container(DonorRepository::class);
		$campaigns = $this->get_from_container(CampaignRepository::class);
		$subscriptions = $this->get_from_container(SubscriptionRepository::class);
		$this->vendor   = new MolliePaymentProvider($this->api_mock, $campaigns, $transactions, $donors, $subscriptions);
		$this->vendor->setLogger($logger);
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
		$this->create_payment_fixture(['show_return_message' => true], function ($args) {
			$this->assertArrayHasKey('redirectUrl', $args);
			$this->assertStringContainsString('kudos_transaction_id=', $args['redirectUrl']);
			return true;
		});
	}

	/**
	 * Test that create_payment does not update the return url.
	 */
	public function test_creates_payment_not_update_redirect_url(): void {
		$this->create_payment_fixture(['show_return_message' => false], function ($args) {
			$this->assertArrayHasKey('redirectUrl', $args);
			$this->assertStringNotContainsString('kudos_transaction_id=', $args['redirectUrl']);
			return true;
		});
	}

	/**
	 * Test that sequence type is set to first for recurring payments.
	 */
	public function test_creates_payment_sets_sequence_type_to_first_for_recurring(): void {
		$this->create_payment_fixture([
			'recurring' => 'true',
			'recurring_frequency' => '1 month',
		], function ($args) {
			$this->assertSame(SequenceType::FIRST, $args['sequenceType']);
			return true;
		});
	}

	/**
	 * Test that non-recurring payments have a sequence type of oneoff.
	 */
	public function test_creates_payment_sets_sequence_type_to_oneoff_for_non_recurring(): void {
		$this->create_payment_fixture([
			'recurring' => 'false',
		], function ($args) {
			$this->assertSame(SequenceType::ONEOFF, $args['sequenceType']);
			return true;
		});
	}

	/**
	 * Test that the Mollie payment metadata is created correctly.
	 */
	public function test_creates_payment_includes_expected_metadata(): void {
		$this->create_payment_fixture([], function ($args) {
			$meta = $args['metadata'];
			$this->assertSame('john.smith@example.com', $meta['email']);
			$this->assertSame('John Smith', $meta['name']);
			$this->assertArrayHasKey('campaign_id', $meta);
			$this->assertArrayHasKey('transaction_id', $meta);
			return true;
		});
	}

	/**
	 * Test that customer
	 */
	public function test_creates_payment_with_customer_id(): void {
		$this->create_payment_fixture([], function ($args) {
			$this->assertSame('cst_abc123', $args['customerId']);
			return true;
		}, 'cst_abc123');
	}

	/**
	 * Test that create_payment returns false if exception thrown by Mollie api.
	 */
	public function test_create_payment_returns_false_on_failure(): void {
		$request_exception_mock = $this->createMock(RequestException::class);
		$payments_mock = $this->getMockBuilder(\stdClass::class)
		                      ->addMethods(['create'])
		                      ->getMock();
		$payments_mock->expects($this->once())
		              ->method('create')
		              ->willThrowException($request_exception_mock);

		$this->api_mock->payments = $payments_mock;

		// Setup required entities
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

		$result = $this->vendor->create_payment($payment_args, $transaction);

		$this->assertFalse($result);
	}

	/**
	 * Test that valid customer is created.
	 */
	public function test_creates_customer(): void {
		$customer_mock = $this->createMock(Customer::class);
		$customers_mock = $this->getMockBuilder(\stdClass::class)
		                       ->addMethods(['create'])
		                       ->getMock();
		$customers_mock->expects($this->once())
		               ->method('create')
		               ->willReturn($customer_mock);

		$this->api_mock->customers = $customers_mock;

		$customer = $this->vendor->create_customer('Jane Doe', 'jane@example.com');

		$this->assertSame($customer_mock, $customer);
	}

	/**
	 * Check the response from get_slug is valid.
	 */
	public function test_get_vendor_slug(): void {
		$this->assertSame('mollie', $this->vendor->get_slug());
	}

	/**
	 * Creates a payment with default values that can be overridden.
	 *
	 * @param array $overrides Values to override.
	 * @param \Closure|null $assert_callback Callback.
	 * @param ?string $vendor_customer_id The vendor customer id.
	 */
	private function create_payment_fixture(array $overrides = [], \Closure $assert_callback = null, ?string $vendor_customer_id = null): array {
		$default_args = [
			'amount' => [ 'currency' => 'EUR', 'value' => '10.00' ],
			'description' => 'Test donation',
			'recurring' => 'false',
			'recurring_length' => '0',
			'value' => 10,
			'return_url' => 'https://example.com',
			'campaign_id' => null, // will be filled dynamically
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

		$payment_mock = $this->createMock(Payment::class);
		$payment_mock->method('getCheckoutUrl')->willReturn('https://mollie.com/checkout/123');
		$payment_mock->id = 'tr_abc123';

		$payments_mock = $this->getMockBuilder(\stdClass::class)
		                      ->addMethods(['create'])
		                      ->getMock();

		if ($assert_callback) {
			$payments_mock->expects($this->once())
			              ->method('create')
			              ->with($this->callback($assert_callback))
			              ->willReturn($payment_mock);
		} else {
			$payments_mock->expects($this->once())
			              ->method('create')
			              ->willReturn($payment_mock);
		}

		$this->api_mock->payments = $payments_mock;

		$checkout_url = $this->vendor->create_payment($payment_args, $transaction, $vendor_customer_id);

		$updated = $transactions->get($transaction_id);

		return [
			'transaction_id' => $transaction_id,
			'transaction'    => $updated,
			'checkout_url'   => $checkout_url,
		];
	}
}
