<?php

namespace Kudos\Migrations;

interface MigrationInterface
{
    /**
     * Migration constructor.
     */
    public function __construct();

    /**
     * Run the migrations and return true if successful.
     */
    public function run();
}
