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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractMigration implements MigrationInterface {

	protected WpDb $wpdb;
	protected LoggerInterface $logger;

	/**
	 * Constructor for migrations.
	 *
	 * @param WpDb                 $wpdb The WordPress database wrapper.
	 * @param LoggerInterface|null $logger Logger instance.
	 */
	public function __construct( WpDb $wpdb, ?LoggerInterface $logger = null ) {
		$this->wpdb   = $wpdb;
		$this->logger = $logger ?? new NullLogger();
	}

	/**
	 * Gets the version number from the static class name.
	 * e.g Version400 will return 4.0.0 as the version number.
	 */
	public function get_version(): string {
		$class = str_replace( __NAMESPACE__ . '\\', '', static::class );
		$num   = filter_var( $class, FILTER_SANITIZE_NUMBER_INT );
		return implode( '.', str_split( $num ) );
	}
}
