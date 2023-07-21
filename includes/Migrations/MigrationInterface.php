<?php

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Service\SettingsService;
use Psr\Log\LoggerInterface;

interface MigrationInterface {

	/**
	 * Migration constructor.
	 */
	public function __construct( LoggerInterface $logger, SettingsService $settings);

	/**
	 * Run the migrations and return true if successful.
	 */
	public function run(): void;
}
