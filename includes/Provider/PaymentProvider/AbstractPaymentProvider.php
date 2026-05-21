<?php
/**
 * Abstract payment provider.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 *
 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Provider\PaymentProvider;

use Exception;
use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Domain\Repository\TransactionRepository;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Provider\AbstractProvider;
use IseardMedia\Kudos\Service\NoticeService;

/**
 * Base class for all payment providers.
 *
 * Concrete providers must extend this class rather than AbstractProvider directly.
 * All payment lifecycle hooks are fired through the final methods below — call
 * them instead of do_action() so that hook names and signatures stay consistent
 * across providers and cannot be accidentally omitted.
 */
abstract class AbstractPaymentProvider extends AbstractProvider implements PaymentProviderInterface {

	protected TransactionRepository $transaction_repository;

	/**
	 * Returns the option name for this provider's mode-keyed cache.
	 */
	abstract protected function get_cache_setting(): ?string;

	/**
	 * Returns provider-specific fields to merge into get_status(). Override in subclasses.
	 *
	 * @param array $data The cached data for the current provider and mode.
	 */
	protected function get_status_extra( array $data ): array {
		return [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_status(): array {
		$setting = $this->get_cache_setting();
		$cache   = $setting ? (array) get_option( $setting, [] ) : [];
		$mode    = $this->get_api_mode();
		$option  = \constant( static::class . '::SETTING_API_KEY_ENCRYPTED_' . strtoupper( $mode ) );
		$key     = $this->get_decrypted_key( $option );
		$data    = isset( $cache[ $mode ] ) ? (array) $cache[ $mode ] : [];
		$stored  = isset( $data['methods'] ) ? (array) $data['methods'] : [];
		$ready   = ! empty( $key ) && ! empty( $stored );
		$methods = array_map(
			fn( $m ) => [
				'id'    => $m['id'],
				'label' => $m['description'],
			],
			$stored
		);
		return array_merge(
			[
				'ready'     => $ready,
				'recurring' => $ready && ! empty( $data['recurring'] ),
				'methods'   => $methods,
			],
			$this->get_status_extra( $data )
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_vendor_ready(): bool {
		return $this->get_status()['ready'];
	}

	/**
	 * Returns the shared webhook REST URL used by all payment providers.
	 */
	public static function get_webhook_url(): string {
		return get_rest_url( null, 'kudos/v1/payment/webhook' );
	}

	/**
	 * Enqueues an async action to process a payment status change for this provider.
	 *
	 * @param string $payment_id The vendor payment or session ID.
	 */
	final protected function enqueue_status_change_action( string $payment_id ): void {
		Utils::enqueue_async_action(
			'kudos_' . static::get_slug() . '_handle_status_change',
			[ 'payment_id' => $payment_id ],
			'kudos-donations'
		);
	}

	/**
	 * Displays an admin notice indicating this provider is in test mode.
	 */
	final protected function show_test_mode_notice(): void {
		NoticeService::notice(
			\sprintf(
				// translators: 1: payment provider name, 2: URL to provider settings page.
				__( '%1$s is currently in test mode, please <a href="%2$s">switch to live</a> before going to production.', 'kudos-donations' ),
				static::get_name(),
				admin_url( 'admin.php?page=kudos-settings&tab=payment&panel=apimode' )
			),
			NoticeService::WARNING,
			false,
			static::get_slug() . '-test-mode'
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sync_transaction_status( int $transaction_id ): ?TransactionEntity {
		$transaction = $this->transaction_repository->get( $transaction_id );
		if ( null === $transaction || null === $transaction->vendor_payment_id ) {
			$this->get_logger()->warning( 'sync_transaction_status: transaction not found or missing vendor ID', [ 'transaction_id' => $transaction_id ] );
			return null;
		}
		try {
			$this->handle_status_change( $transaction->vendor_payment_id );
		} catch ( Exception $e ) {
			$this->get_logger()->error( $e->getMessage(), [ 'transaction_id' => $transaction_id ] );
		}
		return $this->transaction_repository->get( $transaction_id );
	}

	/**
	 * Fires the transaction status hook.
	 *
	 * Hook name is derived from $transaction->status (e.g. kudos_transaction_paid).
	 * The vendor slug is passed as a second parameter so listeners can filter by provider.
	 *
	 * @param TransactionEntity $transaction The transaction whose status changed.
	 */
	final protected function on_transaction_status_changed( TransactionEntity $transaction ): void {
		do_action( "kudos_transaction_$transaction->status", $transaction->id, static::get_slug() );
	}

	/**
	 * Fires the vendor-agnostic refund hook.
	 *
	 * Call this from handle_status_change() when a refund is confirmed by the provider webhook.
	 * The vendor slug is passed as a second parameter so listeners can filter by provider.
	 *
	 * @param TransactionEntity $transaction The refunded transaction.
	 */
	final protected function on_transaction_refunded( TransactionEntity $transaction ): void {
		do_action( 'kudos_transaction_refunded', $transaction->id, static::get_slug() );
	}
}
