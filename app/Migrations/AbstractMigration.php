<?php

namespace Kudos\Migrations;

use Kudos\Helpers\Settings;
use Kudos\Service\LoggerService;

class AbstractMigration
{

    protected const VERSION = '';
    /**
     * @var \Kudos\Service\LoggerService
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