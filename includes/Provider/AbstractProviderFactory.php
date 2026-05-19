<?php
/**
 * Abstract Factory for Providers.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Provider;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Helper\Localization;
use IseardMedia\Kudos\ThirdParty\Symfony\Component\DependencyInjection\ServiceLocator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @template T of ProviderInterface
 */
abstract class AbstractProviderFactory extends AbstractRegistrable {

	private ServiceLocator $provider_locator;

	/**
	 * @param ServiceLocator $provider_locator Used to get class from container.
	 */
	public function __construct( ServiceLocator $provider_locator ) {
		$this->provider_locator = $provider_locator;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		Localization::add_admin( static::get_type_slug(), $this->get_providers() );
		$provider = $this->get_provider();
		if ( null !== $provider ) {
			$provider->init();
		}
	}

	/**
	 * Returns the slug name for the vendor type
	 */
	abstract protected function get_type_slug(): string;

	/**
	 * Get the vendor key for retrieving the option.
	 */
	abstract protected function get_provider_settings_key(): string;

	/**
	 * Get the default vendor name.
	 */
	abstract protected function get_default_vendor(): string;

	/**
	 * Get the interface class for validating vendors.
	 *
	 * @return class-string<T>
	 */
	abstract protected function get_interface_class(): string;

	/**
	 * Returns a provider instance.
	 *
	 * When $slug is null, resolves the currently active provider from settings,
	 * falling back to the default vendor if the saved slug is not registered.
	 *
	 * @param string|null $slug Optional provider slug to look up directly.
	 * @return T|null
	 */
	public function get_provider( ?string $slug = null ): ?ProviderInterface {
		$resolved = $slug ?? (string) get_option( $this->get_provider_settings_key(), $this->get_default_vendor() );
		$slug_map = $this->get_enabled_slug_map();

		$class = null === $slug
			? ( $slug_map[ $resolved ] ?? $slug_map[ $this->get_default_vendor() ] ?? null )
			: ( $slug_map[ $resolved ] ?? null );

		if ( null === $class ) {
			return null;
		}

		try {
			/** @var T $vendor */
			$vendor = $this->provider_locator->get( $class );
			return $vendor;
		} catch ( NotFoundExceptionInterface | ContainerExceptionInterface $e ) {
			$this->get_logger()->error( $e->getMessage() );
			return null;
		}
	}

	/**
	 * Returns the list of available providers for this factory type.
	 *
	 * @return array<int, array{slug: string, label: string}>
	 */
	private function get_providers(): array {
		$providers = [];
		foreach ( $this->get_enabled_slug_map() as $slug => $class ) {
			$providers[] = [
				'slug'  => $slug,
				'label' => $class::get_name(),
				'icon'  => $class::get_icon_svg(),
			];
		}
		return $providers;
	}

	/**
	 * Returns a slug map of enabled providers.
	 *
	 * @return array<string, class-string<ProviderInterface>>
	 */
	private function get_enabled_slug_map(): array {
		$slug_map = [];
		/** @var class-string<ProviderInterface> $class */
		foreach ( array_keys( $this->provider_locator->getProvidedServices() ) as $class ) {
			if ( $class::is_enabled() ) {
				$slug_map[ $class::get_slug() ] = $class;
			}
		}
		return $slug_map;
	}
}
