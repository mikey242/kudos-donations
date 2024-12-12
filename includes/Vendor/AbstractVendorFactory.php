<?php
/**
 * Abstract Factory for Vendors.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Vendor;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Service\NoticeService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;


/**
 * @template T of VendorInterface
 */
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
	 * @return T
	 */
	public function get_vendor(): ?VendorInterface {
		$selected_vendor       = get_option( $this->get_vendor_settings_key(), $this->get_default_vendor() );
		if (!$this->vendor_locator->has($selected_vendor)) {
			NoticeService::add_notice('Cannot find vendor: ' . $selected_vendor . '. Using default vendor: ' . $this->get_default_vendor(), 'error');
			$selected_vendor = $this->get_default_vendor();
			update_option($this->get_vendor_settings_key(), $selected_vendor);
		}

		try {
			return $this->vendor_locator->get($selected_vendor);
		} catch ( NotFoundExceptionInterface | ContainerExceptionInterface $e  ) {
			$this->logger->error($e->getMessage());
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

		// Iterate over the keys in the ServiceLocator
		foreach ($this->vendor_locator->getProvidedServices() as $vendorClass => $vendorService) {
			// Use a static method or reflection to get the name without instantiating the service
			if (method_exists($vendorClass, 'get_name')) {
				$providers[] = $vendorClass::get_name();
			}
		}

		$args[static::get_type_slug()] = $providers;

		return $args;
	}
}