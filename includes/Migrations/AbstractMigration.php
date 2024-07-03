<?php
/**
 * AbstractMigration class.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

namespace IseardMedia\Kudos\Migrations;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractMigration implements MigrationInterface {

	protected \wpdb $wpdb;
	protected LoggerInterface $logger;

	/**
	 * Constructor for migrations.
	 *
	 * @param LoggerInterface|null $logger Logger instance.
	 */
	public function __construct( ?LoggerInterface $logger = null ) {
		global $wpdb;
		$this->wpdb   = $wpdb;
		$this->logger = $logger ?? new NullLogger();
	}
}
