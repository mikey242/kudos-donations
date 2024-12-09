<?php

namespace IseardMedia\Kudos\Vendor\EmailVendor;

interface EmailVendorInterface {
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
	 * @param array $args The arguments.
	 */
	public function send_receipt( array $args ): bool;
}
