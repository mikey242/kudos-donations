<?php
/**
 * Factory for Email Vendor.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Provider\EmailProvider;

use IseardMedia\Kudos\Provider\AbstractProviderFactory;
use IseardMedia\Kudos\Service\MailerService;

/**
 * @extends AbstractProviderFactory<EmailProviderInterface>
 */
class EmailProviderFactory extends AbstractProviderFactory {

	/**
	 * {@inheritDoc}
	 */
	protected function get_type_slug(): string {
		return 'email_vendors';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_provider_settings_key(): string {
		return MailerService::SETTING_EMAIL_VENDOR;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_default_vendor(): string {
		return SMTPProvider::get_slug();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_interface_class(): string {
		return EmailProviderInterface::class;
	}
}
