<?php
/**
 * Interface for delayed classes.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container;

interface Delayed {

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action(): string;

	/**
	 * Get the action priority to use for registering the service.
	 *
	 * @return int Registration action priority to use.
	 */
	public static function get_registration_action_priority(): int;
}
