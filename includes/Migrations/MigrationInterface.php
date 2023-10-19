<?php
/**
 * MigrationInterface.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Service\SettingsService;
use Psr\Log\LoggerInterface;

interface MigrationInterface {

	/**
	 * Migration constructor.
	 *
	 * @param LoggerInterface $logger Logger instance.
	 * @param SettingsService $settings Settings instance.
	 */
	public function __construct( LoggerInterface $logger, SettingsService $settings );

	/**
	 * Run the migrations and return true if successful.
	 */
	public function run(): void;
}
