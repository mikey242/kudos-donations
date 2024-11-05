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

class Version401 extends AbstractMigration {

	/**
	 * {@inheritDoc}
	 */
	public function run(): bool {
		return $this->cleanup();
	}
	/**
	 * Performs additional cleanup not related to prior.
	 */
	private function cleanup(): bool {
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

		// Remove donors table.
		$del_query = 'DROP TABLE IF EXISTS ' . $this->wpdb->prefix . 'kudos_donors';
		$this->wpdb->query( $del_query );

		// Remove transaction table.
		$del_query = 'DROP TABLE IF EXISTS ' . $this->wpdb->prefix . 'kudos_transactions';
		$this->wpdb->query( $del_query );

		// Remove subscriptions table.
		$del_query = 'DROP TABLE IF EXISTS ' . $this->wpdb->prefix . 'kudos_subscriptions';
		$this->wpdb->query( $del_query );

		// Remove log table.
		$del_query = 'DROP TABLE IF EXISTS ' . $this->wpdb->prefix . 'kudos_log';
		$this->wpdb->query( $del_query );
		return true;
	}
}
