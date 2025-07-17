<?php
/**
 * Abstract Factory for Vendors.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Vendor;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;


abstract class AbstractVendorFactory extends AbstractRegistrable {

	private ServiceLocator $vendor_locator;

	/**
	 * Add filter to allow accessing providers in js.
	 */
	public function __construct(ServiceLocator $vendor_locator) {
		$this->vendor_locator = $vendor_locator;
	}

	public function register(): void {
		add_filter( 'kudos_global_localization', [ $this, 'add_providers' ] );
	}

	/**
	 * Returns the slug name for the vendor type
	 */
	abstract protected function get_type_slug(): string;

	/**
	 * Get the vendor key for retrieving the option.
	 */
	abstract protected function get_vendor_settings_key(): string;

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
	 */
	public function get_vendor(): ?VendorInterface {
		$selected_vendor       = (string) get_option( $this->get_vendor_settings_key(), $this->get_default_vendor() );
		/** @var array<string, string> $vendors */
		$vendors = $this->vendor_locator->getProvidedServices();
		/** @var class-string<VendorInterface> $class */
		foreach ($vendors as $class => $_) {
			if($class::get_slug() === $selected_vendor) {
				try {
					/** @var VendorInterface $vendor */
					$vendor = $this->vendor_locator->get($class);
					return $vendor;
				} catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
					$this->get_logger()->error($e->getMessage());
				}
			}
		}

		return null;
	}

	/**
	 * Add the providers to given args.
	 *
	 * @param array $args The existing args.
	 */
	public function add_providers( array $args ): array {
		$providers = [];

		/**
		 * Iterate over the keys in the ServiceLocator
		 *
		 * @var VendorInterface $vendor_class
		 */
		foreach ($this->vendor_locator->getProvidedServices() as $vendor_class => $_) {
			// Use a static method or reflection to get the name without instantiating the service
			if (method_exists($vendor_class, 'get_name')) {
				$providers[] = [
					'slug' => $vendor_class::get_slug(),
					'label' =>$vendor_class::get_name()
				];
			}
		}

		$args[static::get_type_slug()] = $providers;

		return $args;
	}
}