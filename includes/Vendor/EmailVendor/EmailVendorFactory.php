<?php
/**
 * Factory for Email Vendor.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Vendor\EmailVendor;

use IseardMedia\Kudos\Service\MailerService;
use IseardMedia\Kudos\Vendor\AbstractVendorFactory;

/**
 * @extends AbstractVendorFactory<EmailVendorInterface>
 */
class EmailVendorFactory extends AbstractVendorFactory {

	/**
	 * {@inheritDoc}
	 */
	protected function get_type_slug(): string {
		return 'email_vendors';
	}

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
		return SMTPVendor::get_slug();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_interface_class(): string {
		return EmailVendorInterface::class;
	}
}
