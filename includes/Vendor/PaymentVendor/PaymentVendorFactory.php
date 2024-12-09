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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class PaymentVendorFactory extends AbstractVendorFactory {

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
		return 'mollie';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_interface_class(): string {
		return PaymentVendorInterface::class;
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function create( ContainerInterface $container ): ?PaymentVendorInterface {
		$vendor       = get_option( PaymentService::SETTING_VENDOR, 'mollie' );
		$vendor_class = $this->get_vendor( $vendor );
		if ( $vendor_class ) {
			return $container->get( $vendor_class );
		}

		return null;
	}

	/**
	 * Returns the vendor class for the specified name.
	 *
	 * @param string $name The vendor name.
	 * @param string $key The key to return.
	 */
	public function get_vendor( string $name, string $key = 'class' ): ?string {
		$vendors = $this->get_vendors();

		if ( ! isset( $vendors[ $name ][ $key ] ) || ! is_a( $vendors[ $name ]['class'], PaymentVendorInterface::class, true ) ) {
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
			'mollie' => [
				'label' => __( 'Mollie', 'kudos-donations' ),
				'class' => MolliePaymentVendor::class,
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
