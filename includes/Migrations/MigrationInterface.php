<?php
/**
 * MigrationInterface.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Helper\WpDb;

interface MigrationInterface {

	/**
	 * Migration constructor.
	 *
	 * @param WpDb $wpdb WordPress database object.
	 */
	public function __construct( WpDb $wpdb );

	/**
	 * Run the migrations and return true if successful.
	 */
	public function run(): void;
}
