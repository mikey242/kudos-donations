<?php
/**
 * Factory for Payment Vendor.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Provider\PaymentProvider;

use IseardMedia\Kudos\Provider\AbstractProviderFactory;
use IseardMedia\Kudos\Service\PaymentService;

/**
 * @extends AbstractProviderFactory<PaymentProviderInterface>
 */
class PaymentProviderFactory extends AbstractProviderFactory {

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
		return MolliePaymentProvider::get_slug();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_interface_class(): string {
		return PaymentProviderInterface::class;
	}
}
