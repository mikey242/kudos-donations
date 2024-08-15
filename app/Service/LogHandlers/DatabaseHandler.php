<?php
/**
 * Database log handler.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

namespace Kudos\Service\LogHandlers;

use Kudos\Helpers\WpDb;
use Kudos\Service\LoggerService;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class DatabaseHandler extends AbstractProcessingHandler {

	/**
	 * @var WpDb|\wpdb
	 */
	private $wpdb;

	/**
	 * DatabaseHandler constructor.
	 *
	 * @param WpDb   $wpdb The WpDB object.
	 * @param string $level The log level.
	 * @param bool   $bubble Whether to bubble logs or not.
	 */
	public function __construct( WpDb $wpdb, $level = Logger::DEBUG, bool $bubble = true ) {
		$this->wpdb = $wpdb;
		parent::__construct( $level, $bubble );
	}

	/**
	 * Defines how the handler should write a record.
	 * In this case this uses wpdb to write to the database.
	 *
	 * @param array $record The log record to write.
	 */
	protected function write( array $record ): void {

		$wpdb = $this->wpdb;

		$wpdb->insert(
			$wpdb->prefix . LoggerService::TABLE,
			[
				'level'   => $record['level'],
				'message' => $record['message'],
				'context' => $record['context'] ? wp_json_encode( $record['context'] ) : '',
				'date'    => $record['datetime']->format( 'Y-m-d H:i:s' ),
			]
		);
	}
}
