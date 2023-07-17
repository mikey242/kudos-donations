<?php
/**
 * Interface for delayed classes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Infrastructure;

interface Delayed {

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return array Registration actions to use.
	 */
	public static function get_registration_actions(): array;

	/**
	 * Get the action priority to use for registering the service.
	 *
	 * @return int Registration action priority to use.
	 */
	public static function get_registration_action_priority(): int;
}
