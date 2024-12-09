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
use IseardMedia\Kudos\Vendor\AbstractVendorFactory;

class EmailVendorFactory extends AbstractVendorFactory {

	/**
	 * {@inheritDoc}
	 */
	protected function get_vendor_settings_key(): string {
		return MailerService::SETTING_EMAIL_VENDOR;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_default_vendor(): string {
		return 'smtp';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_interface_class(): string {
		return EmailVendorInterface::class;
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
