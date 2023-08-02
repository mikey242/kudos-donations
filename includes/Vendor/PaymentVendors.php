<?php
/**
 * Payment vendor related functions.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Vendor;

use IseardMedia\Kudos\Service\SettingsService;

class PaymentVendors
{
	private string $current_vendor;

	public function __construct(SettingsService $settings) {

		$this->current_vendor = $settings->get_setting( SettingsService::SETTING_NAME_VENDOR, 'mollie' );
	}

	/**
	 * Returns the current vendor class.
	 */
	public function get_current_vendor_class(): ?string {
		return $this->get_vendor($this->current_vendor);
	}

	/**
	 * Returns the vendor class for the specified name.
	 *
	 * @param string $name The vendor name.
	 */
	public function get_vendor(string $name, string $key = 'class'): ?string {
		$vendors = $this->get_vendors();

		if ( ! isset( $vendors[ $name ][$key] ) || ! is_a($vendors[ $name ]['class'], VendorInterface::class, true) ) {
			return null;
		}

		return $vendors[ $name ][ $key ];
	}

	/**
	 * Get an array of registered vendors.
	 *
	 * @return array Array of vendors.
	 */
	public function get_vendors(): array {
		$vendors = [
			'mollie'     => [
				'label' => __( 'Mollie', 'kudos-donations' ),
				'class' => MollieVendor::class,
			],
			'stripe' => [
				'label' => __( 'Stripe', 'kudos-donations' ),
				'class' => StripeVendor::class,
			],
		];

		/**
		 * Filter the array of vendors.
		 *
		 * @param array $vendors Associative array of vendor, including label and class.
		 */
		return apply_filters( 'kudos_payment_vendors', $vendors );
	}
}
