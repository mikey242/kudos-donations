<?php
/**
 * Interface for registrable classes.
 *
 * @link https://code.iseard.media/michael/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container;

interface Registrable {

	/**
	 * Register the service.
	 */
	public function register(): void;

	/**
	 * Enable or disable the registrable.
	 */
	public function is_enabled(): bool;
}
