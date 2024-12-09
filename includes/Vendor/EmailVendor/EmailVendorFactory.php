<?php
/**
 * Factory for Email Vendor.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Vendor\EmailVendor;

use IseardMedia\Kudos\Service\MailerService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class EmailVendorFactory {

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function create( ContainerInterface $container ): ?EmailVendorInterface {
		$vendor       = get_option( MailerService::SETTING_EMAIL_VENDOR, 'smtp' );
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

		if ( ! isset( $vendors[ $name ][ $key ] ) || ! is_a( $vendors[ $name ]['class'], EmailVendorInterface::class, true ) ) {
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
			'smtp' => [
				'label' => __( 'SMTP', 'kudos-donations' ),
				'class' => SMTPVendor::class,
			],
		];

		/**
		 * Filter the array of vendors.
		 *
		 * @param array $vendors Associative array of vendor, including label and class.
		 */
		return apply_filters( 'kudos_email_vendors', $vendors );
	}
}
