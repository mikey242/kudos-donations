<?php
/**
 * Migration for version 4.1.3.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 *
 *  phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
 */

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Domain\PostType\CampaignPostType;

class Version413 extends BaseMigration {

	/**
	 * {@inheritDoc}
	 */
	public function get_migration_jobs(): array {
		return [
			'database' => $this->job( [ $this, 'remove_anonymous_option' ], 'Updating database' ),
			'cleanup'  => $this->job( [ $this, 'cleanup' ], 'Cleaning up' ),
		];
	}

	/**
	 * Remove old anonymous donation option and use it to set email and name field requirements.
	 */
	public function remove_anonymous_option() {
		$this->logger->info( 'Migrating anonymous donor options' );
		$campaigns = CampaignPostType::get_posts();
		foreach ( $campaigns as $campaign ) {
			$anonymous = get_post_meta( $campaign->ID, 'allow_anonymous' );
			if ( $anonymous ) {
				CampaignPostType::save(
					[
						'ID' => $campaign->ID,
						CampaignPostType::META_FIELD_NAME_REQUIRED => false,
						CampaignPostType::META_FIELD_EMAIL_REQUIRED => ! ( 'oneoff' === $campaign->{CampaignPostType::META_FIELD_DONATION_TYPE} ),
					]
				);
			}
		}
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
