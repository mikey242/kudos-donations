<?php
/**
 * Migration service.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Container\Handler;

use IseardMedia\Kudos\Admin\Notice\AdminNotice;
use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use IseardMedia\Kudos\Service\SettingsService;

class MigrationHandler extends AbstractRegistrable implements HasSettingsInterface {

	private const MIGRATE_ACTION           = 'kudos_migrate_action';
	public const SETTING_DB_VERSION        = '_kudos_db_version';
	public const SETTING_MIGRATION_HISTORY = '_kudos_migration_history';
	private string $current_version;
	private string $target_version;
	/**
	 * Array of migrations.
	 *
	 * @var MigrationInterface[]
	 */
	private array $migrations = [];

	/**
	 * Migrator service constructor.
	 */
	public function __construct() {
		$this->current_version = get_option( self::SETTING_DB_VERSION, get_option( SettingsService::SETTING_PLUGIN_VERSION, '1.0' ) );
		$this->target_version  = KUDOS_DB_VERSION;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action(): string {
		return 'admin_init';
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		if ( version_compare( $this->current_version, $this->target_version, '<' ) ) {
			$this->enqueue_assets();
			$this->add_admin_notice();
		}
	}

	/**
	 * Add migration to list.
	 *
	 * @param MigrationInterface $migration The migration to add.
	 */
	public function add_migration( MigrationInterface $migration ): void {
		$this->migrations[] = $migration;
	}

	/**
	 * Returns currently stored migrations.
	 *
	 * @return MigrationInterface[]
	 */
	public function get_migrations(): array {
		return $this->migrations;
	}

	/**
	 * Enqueues the required assets.
	 */
	private function enqueue_assets(): void {
		add_action(
			'admin_enqueue_scripts',
			function () {
				$admin_js = Assets::get_script( 'admin/kudos-admin-migrations.js' );
				wp_enqueue_script(
					'kudos-donations-migrations',
					$admin_js['url'],
					$admin_js['dependencies'],
					$admin_js['version'],
					[
						'in_footer' => true,
					]
				);
			}
		);
	}

	/**
	 * Creates an admin notice with update button.
	 */
	public function add_admin_notice(): void {
		$form  = "<form method='post'>";
		$form .= "<button id='kudos-migrate-button' class='button-secondary confirm' name=" . self::MIGRATE_ACTION . " type='submit' value='kudos_migrate'>";
		$form .= __( 'Update now', 'kudos-donations' );
		$form .= '</button>';
		$form .= "<p id='kudos-migration-status'></p>";
		$form .= '</form>';
		AdminNotice::fancy(
			__(
				'The plugin needs to update your database before you can continue. Please make sure you backup your data before proceeding.',
				'kudos-donations'
			) . '<p>From <strong>' . $this->current_version . '</strong> to <strong>' . KUDOS_DB_VERSION . '</strong></p>' . $form
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_settings(): array {
		return [
			self::SETTING_MIGRATION_HISTORY => [
				'type'         => FieldType::ARRAY,
				'show_in_rest' => false,
				'default'      => [],
			],
			self::SETTING_DB_VERSION        => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
			],
		];
	}
}
