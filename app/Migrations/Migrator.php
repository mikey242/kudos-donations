<?php

namespace IseardMedia\Kudos\Migrations;

use Exception;
use IseardMedia\Kudos\Helpers\Settings;
use IseardMedia\Kudos\Service\LoggerService;

class Migrator
{

    /**
     * @var \IseardMedia\Kudos\Service\LoggerService
     */
    private $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $version
     * @param bool $force
     *
     * @return void
     * @throws \Exception
     */
    public function migrate(string $version, bool $force = false)
    {
        // Remove dots from version.
        $version = str_replace('.', '', $version);

        // Check if migration exists and is valid.
        $migration = __NAMESPACE__ . '\\Version' . $version;
        if ( ! class_exists($migration) && ! $migration instanceof MigrationInterface) {
            throw new Exception("Migration '$version' not found or invalid.");
        }

        // Check if migration already run.
        $migrations = Settings::get_setting('migration_history');
        $library    = is_array($migrations) ? array_flip($migrations) : '';
        if ( ! $force && isset($library[$version])) {
            throw new Exception("Migration '$version' already performed.");
        }

        $type = new $migration($this->logger);
        $type->run();
    }
}