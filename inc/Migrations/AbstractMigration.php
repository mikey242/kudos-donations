<?php

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Helpers\Settings;
use IseardMedia\Kudos\Service\LoggerService;

class AbstractMigration
{

    protected const VERSION = '';
    /**
     * @var \IseardMedia\Kudos\Service\LoggerService
     */
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;

        Settings::update_array(
            'migration_history',
            [static::VERSION],
            true
        );
    }
}