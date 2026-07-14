<?php
/**
 * Migration to skip onboarding on installs that were already set up.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderFactory;
use IseardMedia\Kudos\Provider\PaymentProvider\PaymentProviderInterface;
use IseardMedia\Kudos\Service\SettingsService;

class Version430 extends BaseMigration {

	protected string $version = '4.3.0';

	private PaymentProviderFactory $payment_provider_factory;

	/**
	 * Version430 constructor.
	 *
	 * @param PaymentProviderFactory $payment_provider_factory Used to resolve the active provider.
	 */
	public function __construct( PaymentProviderFactory $payment_provider_factory ) {
		$this->payment_provider_factory = $payment_provider_factory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_auto(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_jobs(): array {
		return [
			'skip_onboarding_if_configured' => $this->job( [ $this, 'skip_onboarding_if_configured' ], 'Checking onboarding status', false ),
		];
	}

	/**
	 * Marks onboarding as dismissed on installs that predate the onboarding banner.
	 *
	 * The banner is driven by SETTING_ONBOARDING_DISMISSED, which defaults to false. Without
	 * this, every existing install would be treated as mid-onboarding: the banner would appear
	 * on every admin page and payment status notices would stay suppressed until dismissed by
	 * hand. A site with a live API key is by definition past onboarding, so mark it done.
	 */
	public function skip_onboarding_if_configured(): void {
		$provider = $this->payment_provider_factory->get_provider();

		if ( ! $provider instanceof PaymentProviderInterface || ! $provider->has_live_key() ) {
			$this->logger->info( 'Onboarding left active: no payment provider configured with a live API key.' );
			return;
		}

		update_option( SettingsService::SETTING_ONBOARDING_DISMISSED, true );
		$this->logger->info( 'Onboarding marked as dismissed: site is already configured.' );
	}
}
