<?php

namespace Kudos\Service;

use DateTimeZone;
use Kudos\Helpers\WpDb;
use Kudos\Service\LogHandlers\DatabaseHandler;
use Monolog\Logger;

class LoggerService extends Logger {

	const TRUNCATE_AT = 100;

	/**
	 * CampaignTable name without prefix.
	 *
	 * @var string
	 */
	public const TABLE = 'kudos_log';
	/**
	 * @var \Kudos\Helpers\WpDb|\wpdb
	 */
	private $wpdb;

	/**
	 * LoggerService constructor.
	 */
	public function __construct() {

		$this->wpdb = new WpDb();

		parent::__construct(
			'kudos',
			[ new DatabaseHandler( $this->wpdb ) ],
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

	/**
	 * Clears the log file.
	 *
	 * @return bool|int
	 */
	public function clear() {

		$wpdb  = $this->wpdb;
		$table = $this->get_table_name();

		return $wpdb->query( "TRUNCATE TABLE `{$table}`" );

	}

	public function get_table_name(): string {
		$wpdb = $this->wpdb;

		return $wpdb->prefix . self::TABLE;

	}

	/**
	 * Truncates the log table keeping
	 * the last 'TRUNCATE_AT' records.
	 *
	 * @return bool|int
	 */
	public function truncate() {
		$wpdb  = $this->wpdb;
		$table = self::get_table_name();

		// Get ID of the oldest row to keep.
		$last_row = $wpdb->get_row( $wpdb->prepare( "
			SELECT `id` FROM {$table}
			ORDER BY `id` DESC
			LIMIT %d,1
		",
			( self::TRUNCATE_AT - 1 ) ) );

		if ( $last_row ) {
			$last_id = $last_row->id;

			return $wpdb->query( $wpdb->prepare( "
				DELETE FROM {$table}
				WHERE `id` < %d
			",
				$last_id ) );
		}

		return false;
	}

	/**
	 * Returns the log table contents as an array.
	 *
	 * @return array|object|null
	 */
	public function get_as_array() {
		$wpdb  = $this->wpdb;
		$table = self::get_table_name();

		return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY `id` DESC LIMIT 100", ARRAY_A );
	}

}
