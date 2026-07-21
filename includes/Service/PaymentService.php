<?php
/**
 * Payment related functions.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Domain\Repository\DonorRepository;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Enum\PaymentStatus;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderFactory;

class PaymentService extends AbstractRegistrable implements HasSettingsInterface {
	public const SETTING_VENDOR        = '_kudos_payment_vendor';
	public const SETTING_VENDOR_STATUS = '_kudos_payment_vendor_status';
	private MailerService $mailer_service;
	private ReceiptService $invoice;
	private TransactionRepository $transaction_repository;
	private DonorRepository $donor_repository;
	private PaymentProviderFactory $payment_provider_factory;

	/**
	 * Payment service constructor.
	 *
	 * @see https://stackoverflow.com/questions/36853791/laravel-dynamic-dependency-injection-for-interface-based-on-user-input
	 *
	 * @param MailerService          $mailer_service Mailer service.
	 * @param ReceiptService         $invoice Receipt service.
	 * @param TransactionRepository  $transaction_repository Transaction repository.
	 * @param DonorRepository        $donor_repository Donor repository.
	 * @param PaymentProviderFactory $payment_provider_factory Payment provider factory.
	 */
	public function __construct( MailerService $mailer_service, ReceiptService $invoice, TransactionRepository $transaction_repository, DonorRepository $donor_repository, PaymentProviderFactory $payment_provider_factory ) {
		$this->mailer_service           = $mailer_service;
		$this->invoice                  = $invoice;
		$this->transaction_repository   = $transaction_repository;
		$this->donor_repository         = $donor_repository;
		$this->payment_provider_factory = $payment_provider_factory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		// Process paid transaction.
		add_action( 'kudos_transaction_paid', [ $this, 'handle_paid_transaction' ] );
		// Second hook is to call another hook that runs when scheduled hook called.
		add_action( 'kudos_process_transaction', [ $this, 'process_transaction' ] );
		// Fallback status check for transactions whose webhook never fired.
		add_action( 'kudos_check_payment_status', [ $this, 'check_payment_status' ] );
		// Virtual status option: computed from stored settings, never written to DB.
		add_filter( 'pre_option_' . self::SETTING_VENDOR_STATUS, [ $this, 'get_vendor_status' ] );
		add_filter( 'pre_update_option_' . self::SETTING_VENDOR_STATUS, fn( $update, $old ) => $old, 10, 2 );
	}

	/**
	 * Returns the current vendor's status derived from stored settings.
	 *
	 * @return array{ready: bool, recurring: bool, steps?: array, methods?: array, account?: string}
	 */
	public function get_vendor_status(): array {
		$provider = $this->payment_provider_factory->get_active_provider();
		if ( null === $provider ) {
			return [
				'ready'     => false,
				'recurring' => false,
				'steps'     => [],
			];
		}
		return $provider->get_status();
	}

	/**
	 * Handle paid transactions.
	 *
	 * @param int $transaction_id The id of the paid transaction.
	 */
	public function handle_paid_transaction( int $transaction_id ) {
		// Updates the invoice number and iterates it for the next one.
		$this->iterate_invoice_number( $transaction_id );
		// Schedule the process hook once payment completed.
		$this->schedule_process_transaction( $transaction_id );
		// Fire additional hooks.
		$this->fire_payment_processed_actions( $transaction_id );
	}

	/**
	 * Fires hooks after a payment is confirmed.
	 *
	 * @param int $transaction_id Local transaction ID.
	 */
	private function fire_payment_processed_actions( int $transaction_id ): void {
		$transaction = $this->transaction_repository->get( $transaction_id );

		if ( ! $transaction ) {
			return;
		}

		$donor = $transaction->donor_id
			? $this->donor_repository->get( $transaction->donor_id )
			: null;

		/**
		 * Fires when a payment is confirmed as paid by the provider.
		 * Provides full context without coupling to provider-specific code.
		 *
		 * @param int   $transaction_id Local transaction ID.
		 * @param array $context {
		 *
		 * @type string|null $sequence_type   'oneoff', 'first', or 'recurring'.
		 * @type int|null    $subscription_id Local subscription ID (null for one-off payments).
		 * @type int|null    $campaign_id     Local campaign ID.
		 * @type int|null    $donor_id        Local donor ID.
		 * @type string|null $donor_email     Donor email address.
		 * @type float       $value           Payment amount.
		 * @type string      $currency        ISO 4217 currency code.
		 * }
		 */
		do_action(
			'kudos_payment_confirmed',
			$transaction_id,
			[
				'vendor'          => $transaction->vendor,
				'sequence_type'   => $transaction->sequence_type,
				'subscription_id' => $transaction->subscription_id,
				'campaign_id'     => $transaction->campaign_id,
				'donor_id'        => $transaction->donor_id,
				'donor_email'     => $donor ? $donor->email : null,
				'value'           => $transaction->value,
				'currency'        => $transaction->currency,
			]
		);

		if ( 'first' === $transaction->sequence_type && $transaction->subscription_id ) {
			/**
			 * Fires when a subscription is successfully created (initial payment confirmed).
			 *
			 * @param int      $subscription_id Local subscription ID.
			 * @param int|null $donor_id        Local donor ID.
			 */
			do_action( 'kudos_subscription_created', $transaction->subscription_id, $transaction->donor_id );
		}
	}

	/**
	 * Adds the invoice number to a transaction and iterates it.
	 *
	 * @param int $transaction_id The id of the post being updated.
	 * @return void
	 */
	public function iterate_invoice_number( int $transaction_id ) {
		$current = (int) get_option( ReceiptService::SETTING_INVOICE_NUMBER );
		$result  = $this->transaction_repository->patch( $transaction_id, [ 'invoice_number' => $current ] );
		if ( $result ) {
			update_option( ReceiptService::SETTING_INVOICE_NUMBER, ( $current + 1 ) );
		} else {
			$this->logger->warning( 'Invoice number not updated' );
		}
	}

	/**
	 * Schedules processing of successful transaction.
	 *
	 * @param int $transaction_id The post id of the transaction.
	 */
	public function schedule_process_transaction( int $transaction_id ): void {
		Utils::schedule_action(
			strtotime( '+1 minute' ),
			'kudos_process_transaction',
			[ $transaction_id ]
		);
	}

	/**
	 * Fallback status sync for transactions whose webhook never arrived.
	 *
	 * @param int $transaction_id Transaction post id.
	 */
	public function check_payment_status( int $transaction_id ): void {
		$transaction = $this->transaction_repository->get( $transaction_id );
		if ( null === $transaction ) {
			return;
		}
		// Already resolved by the webhook or an on-demand status check — nothing to sync.
		if ( ! empty( $transaction->status ) && PaymentStatus::OPEN !== $transaction->status ) {
			return;
		}
		$provider = $this->payment_provider_factory->get_provider( $transaction->vendor );
		if ( null === $provider ) {
			return;
		}
		$provider->sync_transaction_status( $transaction_id );
	}

	/**
	 * Processes the transaction. Used by action scheduler.
	 *
	 * @param int $transaction_id Transaction post id.
	 */
	public function process_transaction( int $transaction_id ) {
		$this->logger->info( 'Processing paid transaction.', [ 'transaction_id' => $transaction_id ] );

		// Generate invoice.
		$this->invoice->generate_receipt( $transaction_id );

		// Send email - email setting is checked in mailer.
		$this->mailer_service->send_receipt( $transaction_id );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_settings(): array {
		return [
			self::SETTING_VENDOR        => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => '',
			],
			self::SETTING_VENDOR_STATUS => [
				'type'         => FieldType::OBJECT,
				'show_in_rest' => [
					'schema' => [
						'type'                 => FieldType::OBJECT,
						'additionalProperties' => true,
						'properties'           => [
							'ready'     => [ 'type' => FieldType::BOOLEAN ],
							'recurring' => [ 'type' => FieldType::BOOLEAN ],
							'account'   => [ 'type' => FieldType::STRING ],
							'steps'     => [
								'type'  => FieldType::ARRAY,
								'items' => [
									'type'       => FieldType::OBJECT,
									'properties' => [
										'id'    => [ 'type' => FieldType::STRING ],
										'label' => [ 'type' => FieldType::STRING ],
										'done'  => [ 'type' => FieldType::BOOLEAN ],
										'panel' => [ 'type' => FieldType::STRING ],
									],
								],
							],
							'methods'   => [
								'type'  => FieldType::ARRAY,
								'items' => [
									'type'       => FieldType::OBJECT,
									'properties' => [
										'id'    => [ 'type' => FieldType::STRING ],
										'label' => [ 'type' => FieldType::STRING ],
									],
								],
							],
						],
					],
				],
				'default'      => [
					'ready'     => false,
					'recurring' => false,
				],
			],
		];
	}
}
