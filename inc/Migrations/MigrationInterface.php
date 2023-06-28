<?php

namespace IseardMedia\Kudos\Migrations;

use Psr\Log\LoggerInterface;

interface MigrationInterface
{
    /**
     * Migration constructor.
     */
    public function __construct(LoggerInterface $logger);

    /**
     * Run the migrations and return true if successful.
     */
    public function run();
}
