<?php
/**
 * AbstractMigration class.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Service\SettingsService;
use Psr\Log\LoggerInterface;

class AbstractMigration {


	protected const VERSION = '';
	protected LoggerInterface $logger;
	protected SettingsService $settings;

	/**
	 * Migration constructor.
	 *
	 * @param LoggerInterface $logger Logger instance.
	 * @param SettingsService $settings Settings service.
	 */
	public function __construct( LoggerInterface $logger, SettingsService $settings ) {
		$this->settings = $settings;
		$this->logger   = $logger;
		$this->settings->update_setting(
			'migration_history',
			[ static::VERSION ]
		);
	}
}
