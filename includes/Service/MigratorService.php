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

use Exception;
use IseardMedia\Kudos\Admin\Notice\AdminNotice;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use Psr\Log\LoggerInterface;

class MigratorService {

	private const MIGRATE_ACTION = 'kudos_migrate_action';
	private LoggerInterface $logger;
	private SettingsService $settings;

	/**
	 * Migrator service constructor.
	 *
	 * @param LoggerInterface $logger Logger instance.
	 */
	public function __construct( LoggerInterface $logger, SettingsService $settings ) {
		$this->settings = $settings;
		$this->logger   = $logger;
		add_action( 'kudos_donations_loaded', [ $this, 'process_form_data' ] );
	}

	/**
	 * Process form data for performing migrations.
	 */
	public function process_form_data(): void {
		if ( isset( $_REQUEST['kudos_migrate_action'] ) ) {
			$action = sanitize_text_field( wp_unslash( $_REQUEST[ self::MIGRATE_ACTION ] ) );
			$nonce  = wp_unslash( $_REQUEST['_wpnonce'] );

			// Check nonce.
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				die();
			}
			$this->settings->update_setting( SettingsService::SETTING_NAME_DB_VERSION, KUDOS_DB_VERSION );
		}
	}

	/**
	 * Check database version number.
	 */
	public function check_database(): bool {
		$db_version = $this->settings->get_setting( SettingsService::SETTING_NAME_DB_VERSION );

		// Make sure that old versions of kudos start at base version.
		if ( ! $db_version ) {
			$db_version = '3.0.0';
		}

		if ( version_compare( $db_version, KUDOS_DB_VERSION, '<' ) ) {
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
				'Kudos Donations database needs updating before you can continue. Please make sure you backup your data before proceeding.',
				'kudos-donations'
			) . $form
		);
	}

	/**
	 * @throws Exception
	 *
	 * @param string $version
	 * @param bool   $force
	 */
	public function migrate( string $version, bool $force = false ): void {
		// Remove dots from version.
		$version = str_replace( '.', '', $version );

		// Check if migration exists and is valid.
		$migration = __NAMESPACE__ . '\\Version' . $version;
		if ( ! class_exists( $migration ) && ! $migration instanceof MigrationInterface ) {
			throw new Exception( "Migration '$version' not found or invalid." );
		}

		// Check if migration already run.
		$migrations = get_option( '_kudos_migration_history' );
		$library    = \is_array( $migrations ) ? array_flip( $migrations ) : '';
		if ( ! $force && isset( $library[ $version ] ) ) {
			throw new Exception( "Migration '$version' already performed." );
		}

		$type = new $migration( $this->logger );
		$type->run();
	}
}
