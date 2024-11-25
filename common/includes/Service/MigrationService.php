<?php
/**
 * Migration service.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Assets;
use IseardMedia\Kudos\Helper\WpDb;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use ReflectionClass;
use ReflectionException;

class MigrationService extends AbstractRegistrable implements HasSettingsInterface {

	private const ACTION_MIGRATE           = 'kudos_migrate_action';
	public const SETTING_DB_VERSION        = '_kudos_db_version';
	public const SETTING_MIGRATION_HISTORY = '_kudos_migration_history';
	public const SETTING_MIGRATION_STATUS  = '_kudos_migration_status';
	public const SETTING_MIGRATION_BUSY    = '_kudos_migration_busy';
	public const SETTING_PLUGIN_VERSION = '_kudos_donations_version';
	private string $current_version;
	private string $target_version;
	/**
	 * Array of migrations.
	 *
	 * @var MigrationInterface[]
	 */
	private array $migrations = [];
	private WpDb $wpdb;

	/**
	 * Migrator service constructor.
	 *
	 * @param WpDb $wpdb The wpdb wrapper service.
	 */
	public function __construct( WpDb $wpdb ) {
		$this->wpdb            = $wpdb;
		$db_version            = get_option( self::SETTING_DB_VERSION );
		$this->current_version = ( false === $db_version || '' === $db_version )
			? get_option( self::SETTING_PLUGIN_VERSION, '' )
			: $db_version;
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
		$busy_migrating = get_option( self::SETTING_MIGRATION_BUSY, false );
		if ( ! $busy_migrating && $this->current_version && version_compare( $this->current_version, $this->target_version, '<' ) ) {
			$this->enqueue_assets();
			$this->add_migration_notice();
		}
	}

	/**
	 * Returns currently stored migrations.
	 *
	 * @return MigrationInterface[]
	 */
	public function get_migrations(): array {
		$this->discover_migrations();
		return $this->migrations;
	}

	/**
	 * Find migrations and determine which ones to add based on the current version.
	 */
	private function discover_migrations() {
		// Check if migrations are already loaded.
		if ( ! empty( $this->migrations ) ) {
			return;
		}

		// Get all the migration files.
		$migration_files = glob( KUDOS_PLUGIN_DIR . 'common/includes/Migrations/*.php' );

		foreach ( $migration_files as $migration_file ) {
			$class_name = $this->get_class_from_file( $migration_file );
			if ( class_exists( $class_name ) ) {
				$reflection = new ReflectionClass( $class_name );
				// Check if it implements MigrationInterface.
				if ( $reflection->implementsInterface( MigrationInterface::class ) && ! $reflection->isAbstract() ) {
					try {
						$migration = $reflection->newInstance( $this->wpdb, $this->logger );
						// Determine if migration should be added based on version.
						if (
							version_compare( $migration->get_version(), $this->current_version, '>' ) &&
							version_compare( $migration->get_version(), $this->target_version, '<=' )
						) {
							$this->add_migration( $migration );
						}
					} catch ( ReflectionException $e ) {
						$this->logger->error( $e->getMessage() );
					}
				}
			}
		}
	}

	/**
	 * Helper to retrieve class name from file path.
	 *
	 * @param string $path The path of the class.
	 */
	private function get_class_from_file( string $path ): ?string {
		$class_name = basename( $path, '.php' );
		return "IseardMedia\\Kudos\\Migrations\\$class_name";
	}


	/**
	 * Add migration to list.
	 *
	 * @param MigrationInterface $migration The migration to add.
	 */
	private function add_migration( MigrationInterface $migration ): void {
		$this->migrations[] = $migration;
	}

	/**
	 * Enqueues the required assets.
	 */
	private function enqueue_assets(): void {
		add_action(
			'admin_enqueue_scripts',
			function () {
				$admin_js = Assets::get_script( 'admin/migrations/kudos-admin-migrations.js' );
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
	private function add_migration_notice(): void {
		$form  = "<form method='post'>";
		$form .= "<button id='kudos-migrate-button' class='button-primary confirm' name=" . self::ACTION_MIGRATE . " type='submit'>";
		$form .= __( 'Update now', 'kudos-donations' );
		$form .= '</button>';
		$form .= "<i id='kudos-migration-status'></i>";
		$form .= '</form>';

		NoticeService::notice(
			'<p><strong>' . __( 'Kudos Donations needs to update your database before you can continue.', 'kudos-donations' ) . '</strong><br/>' . __( 'Please make sure you backup your data before proceeding.', 'kudos-donations' ) . '</p>' . $form,
			NoticeService::INFO,
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
			self::SETTING_PLUGIN_VERSION    => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
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
								'default' => __( 'Kudos Donations: Migration status pending.', 'kudos-donations' ),
							],
						],
					],
				],
			],
		];
	}
}
