<?php

namespace IseardMedia\Kudos\Service\LogHandlers;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use IseardMedia\Kudos\Helpers\WpDb;
use IseardMedia\Kudos\Service\LoggerService;

class DatabaseHandler extends AbstractProcessingHandler
{
    /**
     * @var \IseardMedia\Kudos\Helpers\WpDb|\wpdb
     */
    private $wpdb;

    public function __construct(WpDb $wpdb, $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->wpdb = $wpdb;
        parent::__construct($level, $bubble);
    }

    /**
     * Defines how the handler should write a record.
     * In this case this uses wpdb to write to the database.
     *
     * @param array $record
     */
    protected function write(array $record): void
    {
        $wpdb = $this->wpdb;

        $wpdb->insert($wpdb->prefix . LoggerService::TABLE, [
            'level'   => $record['level'],
            'message' => $record['message'],
            'context' => $record['context'] ? json_encode($record['context']) : '',
            'date'    => $record['datetime']->format('Y-m-d H:i:s'),
        ]);
    }
}
