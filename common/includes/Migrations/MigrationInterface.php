<?php
/**
 * MigrationInterface.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

namespace IseardMedia\Kudos\Migrations;

interface MigrationInterface {
	/**
	 * Returns the version number for the current migration.
	 */
	public function get_version(): string;

	/**
	 * Run the migrations and return true if successful.
	 */
	public function run(): bool;
}
