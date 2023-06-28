<?php

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Helpers\Settings;
use Psr\Log\LoggerInterface;

class AbstractMigration
{

    protected const VERSION = '';
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        Settings::update_array(
            'migration_history',
            [static::VERSION],
            true
        );
    }
}