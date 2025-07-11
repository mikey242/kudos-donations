<?php
/**
 * Payment related functions.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Domain\Repository\RepositoryAwareInterface;
use IseardMedia\Kudos\Domain\Repository\RepositoryAwareTrait;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;

class PaymentService extends AbstractRegistrable implements HasSettingsInterface, RepositoryAwareInterface {

	use RepositoryAwareTrait;

	public const SETTING_VENDOR        = '_kudos_payment_vendor';
	public const SETTING_VENDOR_STATUS = '_kudos_payment_vendor_status';
	private MailerService $mailer_service;
	private InvoiceService $invoice;

	/**
	 * Payment service constructor.
	 *
	 * @see https://stackoverflow.com/questions/36853791/laravel-dynamic-dependency-injection-for-interface-based-on-user-input
	 *
	 * @param MailerService  $mailer_service Mailer service.
	 * @param InvoiceService $invoice Invoice service.
	 */
	public function __construct( MailerService $mailer_service, InvoiceService $invoice ) {
		$this->mailer_service = $mailer_service;
		$this->invoice        = $invoice;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		// Process paid transaction.
		add_action( 'kudos_transaction_paid', [ $this, 'handle_paid_transaction' ] );
		// Second hook is to call another hook that runs when scheduled hook called.
		add_action( 'kudos_process_transaction', [ $this, 'process_transaction' ] );
		// Replace returned get_home_url with app_url if defined.
		add_filter( 'rest_url', [ $this, 'use_alternate_app_url' ], 1, 2 );
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
	}

	/**
	 * Adds the invoice number to a transaction and iterates it.
	 *
	 * @param int $transaction_id The id of the post being updated.
	 * @return void
	 */
	public function iterate_invoice_number( int $transaction_id ) {
		$current      = (int) get_option( InvoiceService::SETTING_INVOICE_NUMBER );
		$transactions = $this->get_repository( TransactionRepository::class );
		$result       = $transactions->patch( $transaction_id, [ 'invoice_number' => $current ] );
		if ( $result ) {
			update_option( InvoiceService::SETTING_INVOICE_NUMBER, ( $current + 1 ) );
		} else {
			$this->logger->error( 'Error updating invoice number' );
		}
	}

	/**
	 * Replaces the home url with the url defined in $_ENV['APP_URL'].
	 *
	 * @param string $url The full URL.
	 * @param string $path The rest route.
	 */
	public function use_alternate_app_url( string $url, string $path ): string {
		if ( isset( $_ENV['APP_URL'] ) && '/kudos/v1/payment/webhook' === $path ) {
			return str_replace( get_home_url(), sanitize_url( $_ENV['APP_URL'] ), $url );
		}
		return $url;
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
	 * Processes the transaction. Used by action scheduler.
	 *
	 * @param int $transaction_id Transaction post id..
	 */
	public function process_transaction( int $transaction_id ): bool {
		$this->logger->debug( 'Processing paid transaction.', [ 'transaction_id' => $transaction_id ] );

		// Generate invoice.
		$this->invoice->generate_invoice( $transaction_id );

		// Send email - email setting is checked in mailer.
		$this->mailer_service->send_receipt( $transaction_id );

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_settings(): array {
		return [
			self::SETTING_VENDOR        => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => 'mollie',
			],
			self::SETTING_VENDOR_STATUS => [
				'type'         => FieldType::OBJECT,
				'show_in_rest' => [
					'schema' => [
						'properties' => [
							'ready'     => [
								'type' => FieldType::BOOLEAN,
							],
							'recurring' => [
								'type' => FieldType::BOOLEAN,
							],
							'text'      => [
								'type' => FieldType::STRING,
							],
						],
					],
				],
				'default'      => [
					'ready'     => false,
					'recurring' => false,
					'text'      => '',
				],
			],
		];
	}
}
