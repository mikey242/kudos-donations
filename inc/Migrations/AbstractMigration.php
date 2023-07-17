<?php

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Service\SettingsService;
use Psr\Log\LoggerInterface;

class AbstractMigration
{

    protected const VERSION = '';

    public function __construct(protected LoggerInterface $logger, protected SettingsService $settings)
    {
        $this->settings->update_setting(
            'migration_history',
            [static::VERSION]
        );
    }
}