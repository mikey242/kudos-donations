<?php
/**
 * Factory for Newsletter Provider.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\KudosPremium\NewsletterProvider;

use IseardMedia\Kudos\Vendor\VendorInterface;
use IseardMedia\KudosPremium\Services\NewsletterService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class NewsletterProviderFactory {
	private static array $providers = [
		'mailchimp'  => [
			'label' => 'Mailchimp',
			'class' => MailchimpProvider::class,
		],
		'mailerlite' => [
			'label' => 'Mailerlite',
			'class' => MailerliteProvider::class,
		],
		'mailpoet'   => [
			'label' => 'MailPoet',
			'class' => MailPoetProvider::class,
		],
		'emailoctopus' => [
			'label' => 'EmailOctopus',
			'class' => EmailOctopus::class,
		],
		'none'         => [
			'label' => 'None',
			'class' => null,
		],
	];

	/**
	 * Add filter to allow accessing providers in js.
	 */
	public function __construct() {
		add_filter( 'kudos_global_localization', [ $this, 'add_providers' ], 50 );
	}

	/**
	 * Create the configured provider.
	 *
	 * @throws ContainerExceptionInterface Thrown if error getting provider class.
	 *
	 * @param ContainerInterface $container The container instance.
	 */
	public function create( ContainerInterface $container ): ?VendorInterface {
		$vendor         = get_option( NewsletterService::SETTING_NEWSLETTER_PROVIDER, 'mailchimp' );
		$provider_class = $this->get_provider( $vendor );
		if ( $provider_class ) {
			return $container->get( $provider_class );
		}
		return null;
	}

	/**
	 * Returns the vendor class for the specified name.
	 *
	 * @param string $name The vendor name.
	 * @param string $key The array key to return.
	 */
	public function get_provider( string $name, string $key = 'class' ): ?string {
		$providers = $this->get_providers();

		if ( ! isset( $providers[ $name ][ $key ] ) || ! is_a( $providers[ $name ]['class'], NewsletterProviderInterface::class, true ) ) {
			return null;
		}

		return $providers[ $name ][ $key ];
	}

	/**
	 * Get an array of registered vendors.
	 *
	 * @return array Array of vendors.
	 */
	public static function get_providers(): array {
		/**
		 * Filter the array of vendors.
		 *
		 * @param array $providers Associative array of vendor, including label and class.
		 */
		return apply_filters( 'kudos_newsletter_providers', self::$providers );
	}

	/**
	 * Add the providers to given args.
	 *
	 * @param array $args The existing args.
	 */
	public function add_providers( array $args ): array {
		$providers                    = static::get_providers();
		$args['newsletter_providers'] = array_map(
			fn( $key, $value ) =>
			[
				'label' => $value['label'],
				'slug'  => $key,
			],
			array_keys( $providers ),
			$providers
		);
		return $args;
	}
}
