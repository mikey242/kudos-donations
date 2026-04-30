<?php
/**
 * Abstract payment provider.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Provider\PaymentProvider;

use IseardMedia\Kudos\Domain\Entity\TransactionEntity;
use IseardMedia\Kudos\Provider\AbstractProvider;

/**
 * Base class for all payment providers.
 *
 * Concrete providers must extend this class rather than AbstractProvider directly.
 * All payment lifecycle hooks are fired through the final methods below — call
 * them instead of do_action() so that hook names and signatures stay consistent
 * across providers and cannot be accidentally omitted.
 */
abstract class AbstractPaymentProvider extends AbstractProvider implements PaymentProviderInterface {

	/**
	 * Fires the transaction status hook.
	 *
	 * Hook name is derived from $transaction->status (e.g. kudos_transaction_paid).
	 * The vendor slug is passed as a second parameter so listeners can filter by provider.
	 *
	 * @param TransactionEntity $transaction The transaction whose status changed.
	 */
	final protected function on_transaction_status_changed( TransactionEntity $transaction ): void {
		do_action( "kudos_transaction_{$transaction->status}", $transaction->id, static::get_slug() );
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
