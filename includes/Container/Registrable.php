<?php
/**
 * Interface for registrable classes.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
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
