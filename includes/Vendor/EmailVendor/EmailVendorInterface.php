<?php

namespace IseardMedia\Kudos\Vendor\EmailVendor;

use IseardMedia\Kudos\Vendor\VendorInterface;

interface EmailVendorInterface extends VendorInterface {
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
	 * @param string $email The email address
	 * @param array $args The template arguments.
	 */
	public function send_receipt( string $email , array $args ): bool;
}
