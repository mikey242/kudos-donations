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
use IseardMedia\Kudos\Helper\Settings;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use Psr\Log\LoggerInterface;

class MigratorService {

	/**
	 * Migrator service constructor.
	 *
	 * @param LoggerInterface $logger Logger instance.
	 */
	public function __construct( private LoggerInterface $logger ) {
		add_action( 'kudos_donations_loaded', [ $this, 'process_form_data' ] );
	}

	/**
	 * Process form data for performing migrations.
	 */
	public function process_form_data(): void {
		if ( isset( $_REQUEST['kudos_action'] ) ) {
			$action = sanitize_text_field( wp_unslash( $_REQUEST['kudos_action'] ) );
			$nonce  = wp_unslash( $_REQUEST['_wpnonce'] );

			// Check nonce.
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				die();
			}
			Settings::update_setting( 'db_version', KUDOS_DB_VERSION );
		}
	}

	/**
	 * Check database version number.
	 */
	public function check_database(): bool {
		$db_version = Settings::get_setting( 'db_version' );

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
		$form .= "<button class='button-secondary confirm' name='kudos_action' type='submit' value='kudos_migrate'>";
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
		$migrations = Settings::get_setting( 'migration_history' );
		$library    = \is_array( $migrations ) ? array_flip( $migrations ) : '';
		if ( ! $force && isset( $library[ $version ] ) ) {
			throw new Exception( "Migration '$version' already performed." );
		}

		$type = new $migration( $this->logger );
		$type->run();
	}
}
