<?php

namespace Kudos\Migrations;

use Exception;
use Kudos\Helpers\Settings;

class Migrator
{

    /**
     * @param string $version
     * @param bool $force
     *
     * @return void
     * @throws \Exception
     */
    public static function migrate(string $version, bool $force = false)
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

        /** @var \Kudos\Migrations\MigrationInterface $type */
        $type = new $migration();
        $type->run();
    }
}