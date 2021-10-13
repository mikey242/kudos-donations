<?php

namespace Kudos\Service;

use DateTimeZone;
use Kudos\Service\LogHandlers\DatabaseHandler;
use Monolog\Logger;
use Kudos\Helpers\WpDb;

class LoggerService extends Logger {

	/**
	 * Table name without prefix
	 *
	 * @var string
	 */
	public const TABLE = 'kudos_log';

	/**
	 * @param \Kudos\Helpers\WpDb $wpdb
	 */
	public function __construct( WpDb $wpdb ) {
		parent::__construct(
			'kudos',
			[ new DatabaseHandler($wpdb) ],
			[],
			new DateTimeZone( wp_timezone_string() )
		);

	}

	/**
	 * Add checks to parent function.
	 *
	 * @param int $level Log level.
	 * @param string $message Message to record.
	 * @param array $context Context array.
	 *
	 * @return bool
	 */
	public function addRecord( int $level, string $message, array $context = [] ): bool {

		// Don't log debug if not enabled.
		if ( self::DEBUG === $level && ! KUDOS_DEBUG ) {
			return false;
		}

		return parent::addRecord( $level, $message, $context );
	}


	public static function get_table_name(): string {
		global $wpdb;
		return $wpdb->prefix . self::TABLE;

	}

	/**
	 * Clears the log file.
	 *
	 * @return bool|int
	 */
	public static function clear() {

		global $wpdb;
		$table = $wpdb->prefix . self::TABLE;
		return $wpdb->query("TRUNCATE TABLE `{$table}`");

	}

	/**
	 * Returns the log table contents as an array.
	 *
	 * @return array|object|null
	 */
	public static function get_as_array() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE;
		return $wpdb->get_results("SELECT * FROM {$table} ORDER BY `date` DESC LIMIT 100",ARRAY_A);
	}

}
