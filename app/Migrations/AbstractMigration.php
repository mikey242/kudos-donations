<?php

namespace Kudos\Migrations;

use Kudos\Helpers\Settings;

class AbstractMigration
{

    protected const VERSION = '';

    public function __construct()
    {
        Settings::update_array(
            'migration_history',
            [static::VERSION],
            true
        );
    }
}