<?php
/**
 * Factory for Payment Vendor.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Provider\PaymentProvider;

use IseardMedia\Kudos\Notice\Notice;
use IseardMedia\Kudos\Notice\NoticeManager;
use IseardMedia\Kudos\Provider\AbstractProviderFactory;
use IseardMedia\Kudos\Service\PaymentService;
use IseardMedia\Kudos\Service\SettingsService;

/**
 * @extends AbstractProviderFactory<PaymentProviderInterface>
 */
class PaymentProviderFactory extends AbstractProviderFactory {

	/**
	 * Runs all payment provider init methods to ensure they can all handle webhooks even when not enabled.
	 *
	 * {@inheritDoc}
	 */
	protected function register_providers(): void {
		foreach ( $this->get_enabled_providers() as $provider ) {
			$provider->init();
		}
		$this->onboarding_notice();

		// Runs a special method only for the active provider (if one resolves).
		$active = $this->get_active_provider();
		if ( null !== $active ) {
			$active->on_active_init();
		}
	}

	/**
	 * Builds the site-wide "complete your setup" notice while onboarding is unfinished.
	 */
	private function onboarding_notice(): void {
		$active = $this->get_active_provider();
		if ( null === $active || ! SettingsService::is_onboarding_active() || empty( $active->get_onboarding_steps() ) ) {
			return;
		}

		NoticeManager::notice(
			new Notice(
				'onboarding-steps',
				\sprintf(
				// translators: %s: URL to the Kudos Donations settings page.
					__( 'Kudos Donations is not ready to receive donations yet. <a href="%s">Complete the setup</a> to get started.', 'kudos-donations' ),
					admin_url( 'admin.php?page=kudos-settings' )
				),
				Notice::WARNING,
			)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_type_slug(): string {
		return 'payment_vendors';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_provider_settings_key(): string {
		return PaymentService::SETTING_VENDOR;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_default_vendor(): string {
		/**
		 * Always return DemoPaymentProvider in demo mode.
		 *
		 * @phpstan-ignore if.alwaysFalse
		 */
		if ( KUDOS_DEMO_MODE ) {
			return DemoPaymentProvider::get_slug();
		}
		return MolliePaymentProvider::get_slug();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_interface_class(): string {
		return PaymentProviderInterface::class;
	}
}
