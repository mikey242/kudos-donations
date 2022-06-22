<?php

namespace Kudos\Migrations;

use Kudos\Helpers\Settings;

class AbstractMigration
{

    private const VERSION = '';

    public function __construct()
    {
        Settings::update_array(
            'migration_history',
            [self::VERSION],
            true
        );
    }
}