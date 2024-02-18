<?php
/**
 * Admin related functions.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller;

use IseardMedia\Kudos\Service\AbstractService;

class Admin extends AbstractService {

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_actions(): array {
		return [ 'admin_init' ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		// TODO add registration.
	}
}
