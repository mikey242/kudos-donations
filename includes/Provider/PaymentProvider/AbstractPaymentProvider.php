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
	 * Provider-specific setup: register hooks, configure the API client, etc.
	 * Called automatically by init() before notices are shown.
	 */
	abstract protected function setup(): void;

	/**
	 * {@inheritDoc}
	 */
	final public function init(): void {
		add_action( 'kudos_' . static::get_slug() . '_handle_status_change', [ $this, 'handle_status_change' ] );
		$this->setup();

		if ( null === $this->get_cache_setting() ) {
			return;
		}

		// While onboarding is unfinished the banner is the single source of guidance.
		if ( SettingsService::is_onboarding_active() ) {
			$this->show_onboarding_notice();
			return;
		}

		$this->show_status_notices();
	}

	/**
	 * Points the user at the settings page, where the onboarding banner walks them through setup.
	 *
	 * Suppressed on Kudos admin pages, where the banner is already on screen.
	 */
	final protected function show_onboarding_notice(): void {
		if ( Utils::is_kudos_admin() ) {
			return;
		}

		NoticeService::notice(
			\sprintf(
			// translators: %s: URL to the Kudos Donations settings page.
				__( 'Kudos Donations is not ready to receive donations yet. <a href="%s">Complete the setup</a> to get started.', 'kudos-donations' ),
				admin_url( 'admin.php?page=kudos-settings' )
			),
			NoticeService::WARNING,
			false,
			'kudos-onboarding-incomplete'
		);
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
		$constant = static::class . '::SETTING_API_KEY_ENCRYPTED_LIVE';
		if ( ! \defined( $constant ) ) {
			return false;
		}
		return '' !== (string) get_option( \constant( $constant ), '' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_vendor_ready(): bool {
		return $this->is_ready();
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
	 * Displays important status notices.
	 */
	final protected function show_status_notices(): void {
		if ( 'test' === $this->get_api_mode() ) {
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
		if ( ! $this->is_vendor_ready() ) {
			NoticeService::notice(
				\sprintf(
				// translators: 1: payment provider name, 2: URL to provider settings page.
					__( '%1$s API keys not set or no payment methods found. Please <a href="%2$s">check your settings</a>.', 'kudos-donations' ),
					static::get_name(),
					admin_url( 'admin.php?page=kudos-settings&tab=payment&panel=apikeys' )
				),
				NoticeService::WARNING,
				false,
				static::get_slug() . '-not-ready'
			);
		}
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
