<?php
/**
 * Email provider interface.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Provider\EmailProvider;

use IseardMedia\Kudos\Provider\ProviderInterface;

interface EmailProviderInterface extends ProviderInterface {
	/**
	 * Sends a message.
	 *
	 * @param string $email Email address.
	 * @param string $header Message header.
	 * @param string $message Message body.
	 */
	public function send_message( string $email, string $header, string $message ): bool;

	/**
	 * Sends receipt to the donor.
	 *
	 * @param string $email The email address.
	 * @param array  $args The template arguments.
	 */
	public function send_receipt( string $email, array $args ): bool;
}
