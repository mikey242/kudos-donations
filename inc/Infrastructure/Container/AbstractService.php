<?php
/**
 * Abstract class for services to extend.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Infrastructure\Container;

abstract class AbstractService implements Delayed, Registrable {

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_actions(): array {
		return [ 'init' ];
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action_priority(): int {
		return 10;
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_enabled(): bool {
		return true;
	}
}
