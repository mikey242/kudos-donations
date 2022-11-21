<?php

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Service\LoggerService;

interface MigrationInterface
{
    /**
     * Migration constructor.
     */
    public function __construct(LoggerService $logger);

    /**
     * Run the migrations and return true if successful.
     */
    public function run();
}
