<?php
/**
 * NewsletterProviderInterface
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

namespace IseardMedia\KudosPremium\NewsletterProvider;

interface NewsletterProviderInterface {
	/**
	 * Add the user to the providers mailing list.
	 *
	 * @param string  $email The user's email address.
	 * @param ?string $name The user's name.
	 */
	public function subscribe_user( string $email, ?string $name = null ): void;
}
