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
use IseardMedia\Kudos\Migrations\MigrationInterface;
use IseardMedia\Kudos\Service\SettingsService;

class MigrationHandler extends AbstractRegistrable {

	private const MIGRATE_ACTION = 'kudos_migrate_action';
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
		$this->current_version = get_option( SettingsService::SETTING_DB_VERSION, get_option( '_kudos_donations_version', '0' ) );
		$this->target_version  = KUDOS_DB_VERSION;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_actions(): array {
		return [ 'admin_init' ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->process_form_data();
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
	 * Runs when the migrate form is submitted.
	 */
	public function process_form_data(): void {
		if ( isset( $_REQUEST[ self::MIGRATE_ACTION ] ) ) {
			$action = sanitize_text_field( wp_unslash( $_REQUEST[ self::MIGRATE_ACTION ] ) );
			$nonce  = wp_unslash( $_REQUEST['_wpnonce'] );

			// Check nonce.
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				die();
			}

			$this->run_migrations();
		}
	}

	/**
	 * Run the migrations in $migrations.
	 */
	private function run_migrations(): void {
		foreach ( $this->migrations as $migration ) {

			// Prevent running migration if already in history.
			if ( \in_array( $migration->get_version(), get_option( SettingsService::SETTING_MIGRATION_HISTORY, [] ), true ) ) {
				$this->logger->debug( 'Migration already applied, skipping', [ 'migration' => $migration->get_version() ] );
				continue;
			}

			$this->logger->debug( 'Running migration: ' . $migration->get_version() );

			$instance = new $migration( $this->logger );

			// Run migration and stop if not successful.
			if ( ! $instance->run() ) {
				$this->logger->error( 'Migration failed.', [ 'migration' => $migration->get_version() ] );
				return;
			}

			// Update migration history.
			$migration_history   = get_option( SettingsService::SETTING_MIGRATION_HISTORY, [] );
			$migration_history[] = $instance->get_version();
			update_option( SettingsService::SETTING_MIGRATION_HISTORY, $migration_history );
		}
		update_option( SettingsService::SETTING_DB_VERSION, KUDOS_DB_VERSION );
	}

	/**
	 * Check database version number and add admin notice to update if necessary.
	 */
	public function check_database(): bool {
		if ( $this->current_version > 0 && version_compare( $this->current_version, $this->target_version, '<' ) ) {
			$this->add_admin_notice();
			return false;
		}
		return true;
	}

	/**
	 * Creates an admin notice with update button.
	 */
	public function add_admin_notice(): void {
		$form  = "<form method='post'>";
		$form .= wp_nonce_field( 'kudos_migrate', '_wpnonce', true, false );
		$form .= "<button class='button-secondary confirm' name=" . self::MIGRATE_ACTION . " type='submit' value='kudos_migrate'>";
		$form .= __( 'Update now', 'kudos-donations' );
		$form .= '</button>';
		$form .= '</form>';
		AdminNotice::fancy(
			__(
				'The plugin needs to update your database before you can continue. Please make sure you backup your data before proceeding.',
				'kudos-donations'
			) . '<p>From <strong>' . $this->current_version . '</strong> to <strong>' . KUDOS_DB_VERSION . '</strong></p>' . $form
		);
	}
}
