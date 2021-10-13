<?php

namespace Kudos\Service\LogHandlers;

use Kudos\Helpers\WpDb;
use Kudos\Service\LoggerService;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class DatabaseHandler extends AbstractProcessingHandler {

	/**
	 * @var \Kudos\Helpers\WpDb|\wpdb
	 */
	private $wpdb;

	public function __construct( WpDb $wpdb, $level = Logger::DEBUG, bool $bubble = true ) {
		$this->wpdb = $wpdb;
		parent::__construct( $level, $bubble );
	}

	protected function write( array $record ): void {

		$wpdb = $this->wpdb;

		$wpdb->insert( $wpdb->prefix . LoggerService::TABLE, [
			'level'   => $record['level'],
			'message' => $record['message'],
			'context' => $record['context'] ? json_encode( $record['context'] ) : '',
			'date' => $record['datetime']->format('Y-m-d H:i:s')
		] );
	}
}