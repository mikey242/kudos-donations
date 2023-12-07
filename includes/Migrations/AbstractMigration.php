<?php
/**
 * AbstractMigration class.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Helper\WpDb;
use IseardMedia\Kudos\Service\SettingsService;

abstract class AbstractMigration implements MigrationInterface {

	protected const VERSION = '';
	protected WpDb $wpdb;

	/**
	 * Migration constructor.
	 *
	 * @param WpDb $wpdb WordPress database object.
	 */
	public function __construct( WpDb $wpdb ) {
		$this->wpdb = $wpdb;
		update_option(
			SettingsService::SETTING_NAME_MIGRATION_HISTORY,
			[ static::VERSION ]
		);
	}
}
