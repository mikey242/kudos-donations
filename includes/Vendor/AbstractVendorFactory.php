<?php
/**
 * Abstract Factory for Vendors.
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Vendor;

use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class AbstractVendorFactory {
	/**
	 * Get the vendor key for retrieving the option.
	 */
	abstract protected function get_vendor_settings_key(): string;

	/**
	 * Get the array of registered vendors.
	 */
	abstract protected function get_vendors(): array;

	/**
	 * Get the default vendor name.
	 */
	abstract protected function get_default_vendor(): string;

	/**
	 * Get the interface class for validating vendors.
	 */
	abstract protected function get_interface_class(): string;

	/**
	 * Create a vendor instance.
	 *
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function create( ContainerInterface $container ): ?object {
		$vendor       = get_option( $this->get_vendor_settings_key(), $this->get_default_vendor() );
		$vendor_class = $this->get_vendor_class( $vendor );

		if ( $vendor_class ) {
			return $container->get( $vendor_class );
		}

		return null;
	}

	/**
	 * Get the vendor class for a given vendor name.
	 *
	 * @param string $name Vendor name.
	 */
	protected function get_vendor_class( string $name ): ?string {
		$vendors = $this->get_vendors();

		if (
			isset( $vendors[ $name ]['class'] ) &&
			is_a( $vendors[ $name ]['class'], $this->get_interface_class(), true )
		) {
			return $vendors[ $name ]['class'];
		}

		return null;
	}
}