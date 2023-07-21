<?php
/**
 * Interface for registrable classes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Infrastructure;

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
