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
			'remove_anonymous_option'         => $this->job( [ $this, 'remove_anonymous_option' ], 'Updating settings' ),
			'link_all_transactions_to_donors' => $this->job( [ $this, 'link_all_transactions_to_donors' ], 'Linking all transactions to donors' ),
			'cleanup_old_tables'              => $this->job( [ $this, 'cleanup' ], 'Cleaning up old tables' ),
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
	 * Link all un-linked transactions to donors using vendor_customer_id.
	 *
	 * @param int $offset Offset of results.
	 * @param int $limit The number of records to fetch.
	 */
	public function link_all_transactions_to_donors( int $offset, int $limit ): int {
		$transactions = get_posts(
			[
				'post_type'        => 'kudos_transaction',
				'post_status'      => 'any',
				'numberposts'      => $limit,
				'offset'           => $offset,
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'suppress_filters' => false,
			]
		);

		if ( empty( $transactions ) ) {
			$this->logger->info( 'No more transactions to link.' );
			return 0;
		}

		foreach ( $transactions as $transaction ) {
			$donor_id           = get_post_meta( $transaction->ID, 'donor_id', true );
			$vendor_customer_id = get_post_meta( $transaction->ID, 'vendor_customer_id', true );
			if ( ! $donor_id && $vendor_customer_id ) {
				$this->logger->info( 'Transaction has no donor_id but does have vendor_customer_id' );
				$donor_args  = [
					'post_type'  => 'kudos_donor',
					'meta_query' => [
						[
							'key'   => 'vendor_customer_id',
							'value' => $vendor_customer_id,
						],
					],
				];
				$donor_query = new \WP_Query( $donor_args );
				if ( $donor_query->have_posts() ) {
					$this->logger->info( 'Donor found, linking to transaction' );
					$donor_post = $donor_query->posts[0];
					$donor_id   = $donor_post->ID;
					update_post_meta( $transaction->ID, 'donor_id', $donor_id );
				}
			}
		}

		return \count( $transactions );
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
