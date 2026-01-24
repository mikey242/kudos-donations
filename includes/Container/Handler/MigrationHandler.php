<?php
/**
 * Repository handler.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\Handler;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use IseardMedia\Kudos\Service\NoticeService;

class MigrationHandler extends AbstractRegistrable implements HasSettingsInterface {

	private const ACTION_MIGRATE           = 'kudos_migrate_action';
	public const SETTING_DB_VERSION        = '_kudos_db_version';
	public const SETTING_MIGRATION_HISTORY = '_kudos_migration_history';
	public const SETTING_MIGRATION_STATUS  = '_kudos_migration_status';
	public const SETTING_PLUGIN_VERSION    = '_kudos_donations_version';

	/**
	 * Array of migrations.
	 *
	 * @var MigrationInterface[]
	 */
	protected array $migrations = [];

	/**
	 * MigrationManager constructor.
	 *
	 * @param iterable $migrations Migrations are injected here by the container.
	 */
	public function __construct( iterable $migrations ) {
		foreach ( $migrations as $migration ) {
			$this->add( $migration );
		}
		usort(
			$this->migrations,
			fn( MigrationInterface $a, MigrationInterface $b ) =>
			version_compare( $a->get_version(), $b->get_version() )
		);
	}

	/**
	 * Add service to list.
	 *
	 * @param MigrationInterface $migration Service.
	 */
	public function add( MigrationInterface $migration ): void {
		$this->migrations[] = $migration;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		if ( $this->should_upgrade() ) {
			$this->enqueue_assets();
			$this->add_migration_notice();
		}
	}

	/**
	 * Determines if a migration should be run.
	 */
	private function should_upgrade(): bool {
		$db_version = get_option( self::SETTING_DB_VERSION );

		$current_version = ! empty( $db_version ) ? $db_version : '';

		$target_version = KUDOS_DB_VERSION;
		return $current_version && version_compare( $current_version, $target_version, '<' );
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
	 * {@inheritDoc}
	 */
	public static function get_registration_action(): string {
		return 'admin_init';
	}

	/**
	 * Creates an admin notice with update button.
	 */
	private function add_migration_notice(): void {
		$form  = "<form method='post'>";
		$form .= "<input type='checkbox' id='kudos-migrate-checkbox' name='kudos-migrate-checkbox' value='1'>";
		$form .= "<label for='kudos-migrate-checkbox'>" . __( 'I have backed up my data', 'kudos-donations' ) . '</label>';
		$form .= "<br><br><button disabled id='kudos-migrate-button' class='button-primary confirm' name=" . self::ACTION_MIGRATE . " type='submit'>";
		$form .= __( 'Update now', 'kudos-donations' );
		$form .= '</button>';
		$form .= "<i id='kudos-migration-status'></i>";
		$form .= '</form>';
		$form .= '<script>
          		    let checkbox = document.getElementById("kudos-migrate-checkbox");
          		    let button = document.getElementById("kudos-migrate-button");
          		    checkbox.addEventListener("change", function() {
          		        button.disabled = !this.checked;
          		    });
				</script>';

		NoticeService::notice(
			'<p><strong>' . __( 'Kudos Donations needs to update your database before you can continue.', 'kudos-donations' ) . '</strong><br/>' . __( 'Please make sure you backup your data before proceeding.', 'kudos-donations' ) . '</p>' . $form,
		);
	}

	/**
	 * Enqueues the required assets.
	 */
	private function enqueue_assets(): void {
		add_action(
			'admin_enqueue_scripts',
			function () {
				$admin_js = Assets::get_script( 'admin/migrations/kudos-admin-migrations.js' );
				if ( null !== $admin_js ) {
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
			}
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_settings(): array {
		return [
			self::SETTING_MIGRATION_HISTORY => [
				'type'         => FieldType::ARRAY,
				'show_in_rest' => false,
				'default'      => [],
			],
			self::SETTING_DB_VERSION        => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => KUDOS_DB_VERSION,
			],
			self::SETTING_PLUGIN_VERSION    => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => KUDOS_VERSION,
			],
			self::SETTING_MIGRATION_STATUS  => [
				'type'         => FieldType::OBJECT,
				'default'      => [],
				'show_in_rest' => [
					'schema' => [
						'type'       => 'object',
						'properties' => [
							'success' => [
								'type'    => 'boolean',
								'default' => false,
							],
							'message' => [
								'type'    => 'string',
								'default' => 'Kudos Donations: Migration status pending.',
							],
						],
					],
				],
			],
		];
	}
}
