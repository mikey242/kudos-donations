<?php
namespace IseardMedia\Kudos\Tests\Service;

use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderFactory;
use IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderInterface;
use IseardMedia\Kudos\Service\MailerService;
use IseardMedia\Kudos\Service\PaymentService;
use IseardMedia\Kudos\Service\ReceiptService;
use IseardMedia\Kudos\Tests\BaseTestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \IseardMedia\Kudos\Service\PaymentService::check_payment_status
 */
class PaymentServiceTest extends BaseTestCase {

	private TransactionRepository $transaction_repository;
	private PaymentProviderFactory $provider_factory;
	private PaymentService $service;

	protected function setUp(): void {
		parent::setUp();

		$this->transaction_repository = $this->get_from_container( TransactionRepository::class );
		$this->provider_factory       = $this->createMock( PaymentProviderFactory::class );

		$this->service = new PaymentService(
			$this->createMock( MailerService::class ),
			$this->createMock( ReceiptService::class ),
			$this->transaction_repository,
			$this->get_from_container( DonorRepository::class ),
			$this->provider_factory
		);
		$this->service->setLogger( $this->createMock( LoggerInterface::class ) );
	}

	/**
	 * A transaction already in a terminal state was resolved by the webhook or an on-demand
	 * status check, so the fallback must not resolve a provider or hit the vendor API.
	 */
	public function test_skips_already_resolved_transaction(): void {
		$id = $this->transaction_repository->insert(
			new TransactionEntity( [ 'title' => 'Paid', 'status' => PaymentStatus::PAID, 'vendor' => 'mollie' ] )
		);

		$this->provider_factory->expects( $this->never() )->method( 'get_provider' );

		$this->service->check_payment_status( $id );
	}

	/**
	 * An open transaction is synced via the provider that owns it.
	 */
	public function test_syncs_open_transaction_via_its_vendor(): void {
		$id = $this->transaction_repository->insert(
			new TransactionEntity( [ 'title' => 'Open', 'status' => PaymentStatus::OPEN, 'vendor' => 'mollie' ] )
		);

		$provider = $this->createMock( PaymentProviderInterface::class );
		$provider->expects( $this->once() )->method( 'sync_transaction_status' )->with( $id );
		$this->provider_factory->expects( $this->once() )->method( 'get_provider' )->with( 'mollie' )->willReturn( $provider );

		$this->service->check_payment_status( $id );
	}

	/**
	 * A transaction that never received a status yet (empty status) must still be synced —
	 * this is precisely the missed-webhook case the fallback exists for.
	 */
	public function test_syncs_transaction_with_no_status_yet(): void {
		$id = $this->transaction_repository->insert(
			new TransactionEntity( [ 'title' => 'Fresh', 'vendor' => 'mollie' ] )
		);

		$provider = $this->createMock( PaymentProviderInterface::class );
		$provider->expects( $this->once() )->method( 'sync_transaction_status' )->with( $id );
		$this->provider_factory->expects( $this->once() )->method( 'get_provider' )->willReturn( $provider );

		$this->service->check_payment_status( $id );
	}
}