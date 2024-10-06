<?php
/**
 * Factory for Payment Vendor.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Vendor;

use IseardMedia\Kudos\Service\PaymentService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class VendorFactory
{

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function create(ContainerInterface $container): ?VendorInterface
	{
		$vendor = get_option( PaymentService::SETTING_VENDOR, 'mollie' );
		$vendorClass = $this->get_vendor($vendor);
		if ($vendorClass) {
			return $container->get($vendorClass);
		}
		return null;
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
		];

		/**
		 * Filter the array of vendors.
		 *
		 * @param array $vendors Associative array of vendor, including label and class.
		 */
		return apply_filters( 'kudos_payment_vendors', $vendors );
	}
}