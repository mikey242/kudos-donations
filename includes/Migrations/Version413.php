<?php
/**
 * Migration for version 4.1.3.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 *
 *  phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
 */

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Helper\WpDb;

class Version413 extends BaseMigration {

	protected string $version = '4.1.3';

	protected WpDb $wpdb;

	/**
	 * Constructor for migrations.
	 *
	 * @param WpDb $wpdb The WordPress database wrapper.
	 */
	public function __construct( WpDb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_jobs(): array {
		return [
			'remove_anonymous_option' => $this->job( [ $this, 'remove_anonymous_option' ], 'Updating settings' ),
			'cleanup_old_tables'      => $this->job( [ $this, 'cleanup' ], 'Cleaning up old tables' ),
		];
	}

	/**
	 * Remove old anonymous donation option and use it to set email and name field requirements.
	 */
	public function remove_anonymous_option(): int {
		$campaigns = get_posts(
			[
				'post_type'      => 'kudos_campaign',
				'posts_per_page' => -1,
			]
		);
		foreach ( $campaigns as $campaign ) {
			$anonymous = get_post_meta( $campaign->ID, 'allow_anonymous', true );
			if ( $anonymous ) {
				update_post_meta( $campaign->ID, 'name_required', false );
				update_post_meta( $campaign->ID, 'email_required', ! ( 'oneoff' === ( $campaign->donation_type ?? 'oneoff' ) ) );
			}
		}
		return 1;
	}

	/**
	 * Performs additional cleanup not related to prior.
	 */
	public function cleanup(): int {
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
		$tables = [ 'kudos_campaigns', 'kudos_donors', 'kudos_transactions', 'kudos_subscriptions', 'kudos_log' ];
		foreach ( $tables as $table ) {
			$table_name = $this->wpdb->prefix . $table;
			$this->logger->debug( "Removing table $table_name" );
			$this->wpdb->query( 'DROP TABLE IF EXISTS ' . $table_name );
		}
		return 1;
	}
}
