<?php
/**
 * Migration service.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Admin\Notice\AdminNotice;
use IseardMedia\Kudos\Helper\WpDb;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use Psr\Log\LoggerInterface;

class MigratorService {

	private const MIGRATE_ACTION = 'kudos_migrate_action';
	private LoggerInterface $logger;
	private SettingsService $settings;
	private WpDb $wpdb;
	private string $current_version;
	private string $target_version;
	private array $migrations = [];

	/**
	 * Migrator service constructor.
	 *
	 * @param LoggerInterface $logger Logger instance.
	 * @param SettingsService $settings Settings service.
	 * @param WpDb            $wpdb WordPress database object.
	 */
	public function __construct( LoggerInterface $logger, SettingsService $settings, WpDb $wpdb ) {
		$this->settings        = $settings;
		$this->logger          = $logger;
		$this->wpdb            = $wpdb;
		$this->current_version = $this->settings->get_setting( SettingsService::SETTING_NAME_DB_VERSION, '3.0.0' );
		$this->target_version  = KUDOS_DB_VERSION;
		add_action( 'kudos_donations_loaded', [ $this, 'process_form_data' ] );
	}

	/**
	 * Add migration to list.
	 *
	 * @param string $migration The migration to add.
	 */
	public function add_migration( string $migration ): void {
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

			$this->discover_migrations();
			$this->run_migrations();
		}
	}

	/**
	 * Run the migrations in $migrations.
	 */
	private function run_migrations(): void {
		$this->logger->debug( 'Processing migrations.', $this->migrations );
		foreach ( $this->migrations as $migration ) {
			$class_name = basename( $migration, '.php' );
			$migration  = 'IseardMedia\\Kudos\\Migrations\\' . $class_name;
			if ( ! class_exists( $migration ) || ! is_subclass_of( $migration, MigrationInterface::class ) ) {
				continue;
			}
			$instance = new $migration( $this->wpdb );
			$instance->run();
		}
		update_option( SettingsService::SETTING_NAME_DB_VERSION, KUDOS_DB_VERSION );
	}

	/**
	 * Check database version number and add admin notice to update if necessary.
	 */
	public function check_database(): bool {
		if ( version_compare( $this->current_version, $this->target_version, '<' ) ) {
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
		( new AdminNotice() )->info(
			__(
				'Kudos Donations needs to update your database before you can continue. Please make sure you backup your data before proceeding.',
				'kudos-donations'
			) . '<p>From <strong>' . $this->current_version . '</strong> to <strong>' . KUDOS_DB_VERSION . '</strong></p>' . $form
		);
	}

	/**
	 * Find migrations and add relevant ones to $this->migrations.
	 */
	public function discover_migrations() {
		$migration_files = glob( KUDOS_PLUGIN_DIR . 'includes/Migrations/*.php' );

		foreach ( $migration_files as $migration ) {
			$file_name  = basename( $migration, '.php' );
			$candidates = (int) filter_var( $file_name, FILTER_SANITIZE_NUMBER_INT );
			if ( $candidates ) {
				$candidate_version = implode( '.', str_split( (string) $candidates ) );
				if ( version_compare( $candidate_version, $this->target_version, '<=' ) && version_compare( $candidate_version, $this->current_version, '>' ) ) {
					$this->add_migration( $file_name );
				}
			}
		}
	}
}