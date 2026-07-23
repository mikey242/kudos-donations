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
use IseardMedia\Kudos\Notice\Notice;
use IseardMedia\Kudos\Provider\AbstractProvider;
use IseardMedia\Kudos\Service\SettingsService;

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
	 * {@inheritDoc}
	 */
	public function init(): void {
		add_action( 'kudos_' . static::get_slug() . '_handle_status_change', [ $this, 'handle_status_change' ] );
		$this->register_key_hooks();
		$this->on_init();
	}

	/**
	 * Use to run custom init code.
	 */
	protected function on_init(): void {}

	/**
	 * {@inheritDoc}
	 *
	 * Reads the provider's own `SETTING_API_MODE` option, defaulting to test. Providers that
	 * expose no mode setting (e.g. demo) fall through to 'test'.
	 */
	public function get_api_mode(): string {
		$const = static::class . '::SETTING_API_MODE';
		return \defined( $const ) ? (string) get_option( \constant( $const ), 'test' ) : 'test';
	}

	/**
	 * Wires the API-key option hooks: validate/encrypt on save, then refresh the cached vendor
	 * data once the encrypted key lands. Providers that expose no API keys (e.g. demo) define
	 * none of these constants and are simply skipped.
	 */
	private function register_key_hooks(): void {
		foreach ( [ 'LIVE', 'TEST' ] as $mode ) {
			$plain_key     = static::class . '::SETTING_API_KEY_' . $mode;
			$encrypted_key = static::class . '::SETTING_API_KEY_ENCRYPTED_' . $mode;
			if ( \defined( $plain_key ) ) {
				add_filter( 'pre_update_option_' . \constant( $plain_key ), [ $this, 'handle_key_update' ], 10, 3 );
			}
			if ( \defined( $encrypted_key ) ) {
				add_action( 'update_option_' . \constant( $encrypted_key ), [ $this, 'handle_key_updated' ], 10, 2 );
			}
		}
	}

	/**
	 * Validates and persists an updated API key. Overridden by key-based providers; the base
	 * implementation is a no-op passthrough for providers that expose no keys.
	 *
	 * @param string $value      The new (plain) value being saved.
	 * @param string $_old_value The previous value.
	 * @param string $_option    The option name being updated.
	 */
	public function handle_key_update( string $value, string $_old_value, string $_option ): string {
		return $value;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Returns test-mode and not-ready warnings for the active vendor. Empty during onboarding or
	 * for providers with no cache setting (e.g. demo).
	 */
	public function get_status_notices(): array {
		if ( null === $this->get_cache_setting() || SettingsService::is_onboarding_active() ) {
			return [];
		}

		$notices = [];

		if ( 'test' === $this->get_api_mode() ) {
			$notices[] = new Notice(
				'test-mode',
				\sprintf(
				// translators: 1: payment provider name, 2: URL to provider settings page.
					__( '%1$s is currently in test mode, please <a href="%2$s">switch to live</a> before going to production.', 'kudos-donations' ),
					static::get_name(),
					admin_url( 'admin.php?page=kudos-settings&tab=payment&panel=apimode' )
				),
				Notice::WARNING
			);
		}

		if ( ! $this->is_vendor_ready() ) {
			$notices[] = new Notice(
				'vendor-ready',
				\sprintf(
				// translators: 1: payment provider name, 2: URL to provider settings page.
					__( '%1$s API keys not set or no payment methods found. Please <a href="%2$s">check your settings</a>.', 'kudos-donations' ),
					static::get_name(),
					admin_url( 'admin.php?page=kudos-settings&tab=payment&panel=apikeys' )
				),
				Notice::WARNING
			);
		}

		return $notices;
	}

	/**
	 * Returns provider-specific fields to merge into get_status(). Override in subclasses.
	 *
	 * @param array $data The cached data for the current provider and mode.
	 */
	protected function get_status_extra( array $data ): array {
		return [];
	}

	/**
	 * Triggers a refresh after the encrypted key option is successfully saved.
	 *
	 * @param string $_old_value Previous option value.
	 * @param string $new_value  New option value (empty when key was cleared).
	 */
	public function handle_key_updated( string $_old_value, string $new_value ): void {
		if ( ! $new_value ) {
			return;
		}
		$this->refresh();
	}

	/**
	 * Returns the decrypted API key for the current mode.
	 */
	protected function get_api_key(): string {
		$mode   = $this->get_api_mode();
		$option = \constant( static::class . '::SETTING_API_KEY_ENCRYPTED_' . strtoupper( $mode ) );
		return $this->get_decrypted_key( $option, admin_url( 'admin.php?page=kudos-settings&tab=payment&panel=apikeys' ) );
	}

	/**
	 * Returns the cached data stored for the current API mode.
	 */
	private function get_mode_data(): array {
		$setting = $this->get_cache_setting();
		$cache   = $setting ? (array) get_option( $setting, [] ) : [];
		$mode    = $this->get_api_mode();
		return isset( $cache[ $mode ] ) ? (array) $cache[ $mode ] : [];
	}

	/**
	 * Whether the provider has a usable key and payment methods for the current mode.
	 */
	private function is_ready(): bool {
		$stored = (array) ( $this->get_mode_data()['methods'] ?? [] );
		return ! empty( $this->get_api_key() ) && ! empty( $stored );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_status(): array {
		$data    = $this->get_mode_data();
		$stored  = isset( $data['methods'] ) ? (array) $data['methods'] : [];
		$ready   = $this->is_ready();
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
				'steps'     => $this->get_onboarding_steps(),
			],
			$this->get_status_extra( $data )
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * The default flow for a key-based provider: add a live key, then go live. Providers with
	 * extra setup (a webhook secret, say) can append to these.
	 */
	public function get_onboarding_steps(): array {
		return [
			[
				'id'    => 'apikeys',
				'label' => __( 'Enter live API key', 'kudos-donations' ),
				'done'  => $this->has_live_key(),
				'panel' => 'apikeys',
			],
			[
				'id'    => 'livemode',
				'label' => __( 'Switch to live mode', 'kudos-donations' ),
				'done'  => 'live' === $this->get_api_mode(),
				'panel' => 'apimode',
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function has_live_key(): bool {
		return $this->mode_has_key( 'live' );
	}

	/**
	 * Whether the given mode has a stored (encrypted) API key.
	 *
	 * @param string $mode The API mode ('test' or 'live').
	 */
	protected function mode_has_key( string $mode ): bool {
		$constant = static::class . '::SETTING_API_KEY_ENCRYPTED_' . strtoupper( $mode );
		if ( ! \defined( $constant ) ) {
			return false;
		}
		return '' !== (string) get_option( \constant( $constant ), '' );
	}

	/**
	 * Activates the given mode, but only when the current mode has no key configured.
	 *
	 * @param string $mode The mode the key being saved belongs to.
	 */
	protected function maybe_activate_mode( string $mode ): void {
		$constant = static::class . '::SETTING_API_MODE';
		if ( ! \defined( $constant ) ) {
			return;
		}
		if ( $this->mode_has_key( $this->get_api_mode() ) ) {
			return;
		}
		update_option( \constant( $constant ), $mode );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_vendor_ready(): bool {
		return $this->is_ready();
	}

	/**
	 * Returns this provider's vendor-specific webhook REST URL.
	 */
	protected static function get_webhook_url(): string {
		return get_rest_url( null, 'kudos/v1/payment/webhook/' . static::get_slug() );
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
