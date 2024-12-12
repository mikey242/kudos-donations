<?php
/**
 * Factory for Payment Vendor.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Vendor\PaymentVendor;

use IseardMedia\Kudos\Service\PaymentService;
use IseardMedia\Kudos\Vendor\AbstractVendorFactory;

class PaymentVendorFactory extends AbstractVendorFactory {

	/**
	 * {@inheritDoc}
	 */
	protected function get_type_slug(): string {
		return 'payment_vendors';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_vendor_settings_key(): string {
		return PaymentService::SETTING_VENDOR;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_default_vendor(): string {
		return MolliePaymentVendor::get_slug();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_interface_class(): string {
		return PaymentVendorInterface::class;
	}
}
