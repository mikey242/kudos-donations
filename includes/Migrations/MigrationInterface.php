<?php
/**
 * MigrationInterface.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

namespace IseardMedia\Kudos\Migrations;

/**
 * Interface defining a migration.
 *
 * @phpstan-type MigrationJob array{
 *       callback: callable,
 *       chunked?: bool,
 *       args?: array,
 *       label?: string
 *   }
 */
interface MigrationInterface {
	/**
	 * Returns the version number for the current migration.
	 */
	public function get_version(): string;

	/**
	 * Returns an ordered list of migration steps.
	 *
	 * Each step is defined as a key-value pair where the key is a unique step identifier,
	 * and the value is an associative array describing how the step behaves.
	 *
	 * @phpstan-return array<string, MigrationJob>
	 */
	public function get_jobs(): array;

	/**
	 * Checks if migration job is complete.
	 *
	 * @param string $job The job method name.
	 */
	public function is_complete( string $job ): bool;
}
