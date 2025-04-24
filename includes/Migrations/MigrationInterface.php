<?php
/**
 * MigrationInterface.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
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
	public function get_migration_jobs(): array;

	/**
	 * Returns a generic summary of progress for frontend display.
	 * Automatically detects current step and offsets for chunked steps.
	 */
	public function get_progress_summary(): array;

	/**
	 * Run the step with provided limit.
	 */
	public function step(): bool;

	/**
	 * Return true if the migration is complete.
	 */
	public function is_complete(): bool;
}
