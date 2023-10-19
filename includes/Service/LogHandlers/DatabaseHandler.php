<?php
/**
 * DatabaseHandler for logging.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

namespace IseardMedia\Kudos\Service\LogHandlers;

use IseardMedia\Kudos\Helper\WpDb;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class DatabaseHandler extends AbstractProcessingHandler {

	private \wpdb $wpdb;

	/**
	 * Constructor.
	 *
	 * @param WpDb|\wpdb $wpdb WordPress database.
	 * @param string     $level Level of logging to use.
	 * @param bool       $bubble Should the log bubble or not.
	 */
	public function __construct( WpDb $wpdb, $level = Logger::DEBUG, bool $bubble = true ) {
		$this->wpdb = $wpdb;
		parent::__construct( $level, $bubble );
	}

	/**
	 * Defines how the handler should write a record.
	 * In this case this uses wpdb to write to the database.
	 *
	 * @param array $record Log record to write.
	 */
	protected function write( array $record ): void {
		$wpdb = $this->wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'kudos_log',
			[
				'level'   => $record['level'],
				'message' => $record['message'],
				'context' => $record['context'] ? wp_json_encode( $record['context'] ) : '',
				'date'    => $record['datetime']->format( 'Y-m-d H:i:s' ),
			]
		);
	}
}
