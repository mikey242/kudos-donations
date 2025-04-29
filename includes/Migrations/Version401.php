<?php
/**
 * Migration for version 4.0.0.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 *
 *  phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
 */

namespace IseardMedia\Kudos\Migrations;

class Version401 extends BaseMigration {

	/**
	 * {@inheritDoc}
	 */
	public function get_migration_jobs(): array {
		return [
			'cleanup' => $this->job( [ $this, 'cleanup' ], 'Cleaning up' ),
		];
	}

	/**
	 * Performs additional cleanup not related to prior.
	 */
	public function cleanup(): bool {
		$this->logger->info( 'Cleaning up old Kudos Donations data' );

		// Remove old options.
		delete_option( '_kudos_vendor_mollie' );
		delete_option( '_kudos_smtp_host' );
		delete_option( '_kudos_smtp_port' );
		delete_option( '_kudos_smtp_encryption' );
		delete_option( '_kudos_smtp_autotls' );
		delete_option( '_kudos_smtp_username' );
		delete_option( '_kudos_smtp_from' );
		delete_option( '_kudos_campaigns' );

		// Remove old tables.
		$tables = [ 'kudos_donors', 'kudos_transactions', 'kudos_subscriptions', 'kudos_log' ];
		foreach ( $tables as $table ) {
			$this->wpdb->query( 'DROP TABLE IF EXISTS ' . $this->wpdb->prefix . $table );
		}
		return true;
	}
}
