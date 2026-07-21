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
	 * Memoised map of enabled providers (slug => class-string). The enabled set is fixed for the
	 * request, so it is computed once on first access.
	 *
	 * @var array<string, class-string<ProviderInterface>>|null
	 */
	private ?array $enabled_slug_map = null;

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
		Localization::add_admin( static::get_type_slug(), $this->get_provider_metadata() );
		$this->register_providers();
	}

	/**
	 * Initialises the active provider this factory manages.
	 */
	protected function register_providers(): void {
		$provider = $this->get_active_provider();
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
	 * Returns a provider instance for the given provider slug.
	 *
	 * @param string $slug Provider slug to look up directly.
	 * @return T|null
	 */
	public function get_provider( string $slug ): ?ProviderInterface {
		$slug_map = $this->get_enabled_slug_map();
		$class    = ( $slug_map[ $slug ] ?? null );

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
	 * Gets the currently active provider or the fallback default if none set.
	 *
	 * @return T|null
	 */
	public function get_active_provider(): ?ProviderInterface {
		$resolved = get_option( $this->get_provider_settings_key(), $this->get_default_vendor() );
		return $resolved ? $this->get_provider( $resolved ) : $this->get_provider( $this->get_default_vendor() );
	}

	/**
	 * Resolves every enabled provider. Building a provider is cheap — its heavy dependencies
	 * (API clients) are lazy container proxies, so nothing connects or decrypts here.
	 *
	 * @return array<string, T>
	 */
	protected function get_enabled_providers(): array {
		$providers = [];
		foreach ( array_keys( $this->get_enabled_slug_map() ) as $slug ) {
			$provider = $this->get_provider( $slug );
			if ( null !== $provider ) {
				$providers[ $slug ] = $provider;
			}
		}
		return $providers;
	}

	/**
	 * Returns admin-UI metadata for each enabled provider (not the providers themselves).
	 *
	 * @return array<int, array{slug: string, label: string, icon: string}>
	 */
	private function get_provider_metadata(): array {
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
	 * Returns a slug map of enabled providers, computed once per request.
	 *
	 * @return array<string, class-string<ProviderInterface>>
	 */
	private function get_enabled_slug_map(): array {
		if ( null !== $this->enabled_slug_map ) {
			return $this->enabled_slug_map;
		}

		$slug_map = [];
		/** @var class-string<ProviderInterface> $class */
		foreach ( array_keys( $this->provider_locator->getProvidedServices() ) as $class ) {
			if ( $class::is_enabled() ) {
				$slug_map[ $class::get_slug() ] = $class;
			}
		}

		$this->enabled_slug_map = $slug_map;
		return $slug_map;
	}
}
