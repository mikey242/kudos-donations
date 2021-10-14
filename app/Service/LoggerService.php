<?php

namespace Kudos\Service;

use DateTimeZone;
use Kudos\Service\LogHandlers\DatabaseHandler;
use Monolog\Logger;
use Kudos\Helpers\WpDb;

class LoggerService extends Logger {

	const TRUNCATE_AT = 100;

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
			[ new DatabaseHandler( $wpdb ) ],
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
		/** @var \wpdb $wpdb */
		$wpdb = new WpDb();

		return $wpdb->prefix . self::TABLE;

	}

	/**
	 * Clears the log file.
	 *
	 * @return bool|int
	 */
	public static function clear() {

		/** @var \wpdb $wpdb */
		$wpdb  = new WpDb();
		$table = self::get_table_name();

		return $wpdb->query( "TRUNCATE TABLE `{$table}`" );

	}

	/**
	 * Truncates the log table keeping
	 * the last 'TRUNCATE_AT' records.
	 *
	 * @return bool|int
	 */
	public static function truncate() {
		/** @var \wpdb $wpdb */
		$wpdb  = new WpDb();
		$table = self::get_table_name();

		$last_row = $wpdb->get_row( $wpdb->prepare("
			SELECT `id` FROM {$table}
			LIMIT %d,1
		", (self::TRUNCATE_AT - 1) ) );

		if($last_row) {
			$last_id = $last_row->id;
			return $wpdb->query($wpdb->prepare("
				DELETE FROM {$table}
				WHERE `id` > %d
			", $last_id));

		}

		return false;
	}

	/**
	 * Returns the log table contents as an array.
	 *
	 * @return array|object|null
	 */
	public static function get_as_array() {
		global $wpdb;
		$table = self::get_table_name();

		return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY `id` DESC LIMIT 100", ARRAY_A );
	}

}
