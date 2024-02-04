<?php
/**
 * Migration for version 4.0.0.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 *
 *  phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
 */

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;

class Version400 extends AbstractMigration {

	private array $cache;

	/**
	 * {@inheritDoc}
	 */
	public function run(): void {
		$this->migrate_donors_to_posts();
		$this->migrate_campaigns_to_posts();
		$this->migrate_transactions_to_posts();
	}

	/**
	 * Migrates donors from custom table to custom post type.
	 */
	public function migrate_donors_to_posts(): void {
		$table_name = $this->wpdb->prefix . 'kudos_donors';
		$query      = "SELECT * FROM {$table_name}";
		$results    = $this->wpdb->get_results( $query );

		foreach ( $results as $donor ) {

			// Create post and store ID.
			$new_id = wp_insert_post(
				[
					'post_type'   => DonorPostType::get_slug(),
					'post_date'   => $donor->created,
					'post_status' => 'publish',
				]
			);

			// Add post meta to new post.
			update_post_meta( $new_id, DonorPostType::META_FIELD_EMAIL, $donor->email );
			update_post_meta( $new_id, DonorPostType::META_FIELD_NAME, $donor->name );
			update_post_meta( $new_id, DonorPostType::META_FIELD_BUSINESS_NAME, $donor->business_name );
			update_post_meta( $new_id, DonorPostType::META_FIELD_STREET, $donor->street );
			update_post_meta( $new_id, DonorPostType::META_FIELD_POSTCODE, $donor->postcode );
			update_post_meta( $new_id, DonorPostType::META_FIELD_CITY, $donor->city );
			update_post_meta( $new_id, DonorPostType::META_FIELD_COUNTRY, $donor->country );
			update_post_meta( $new_id, DonorPostType::META_FIELD_MODE, $donor->mode );
			update_post_meta( $new_id, DonorPostType::META_FIELD_VENDOR_CUSTOMER_ID, $donor->customer_id );
			$this->cache['donor_id'][ $donor->customer_id ] = $new_id;
		}
	}

	/**
	 * Migrate campaigns from a settings array to CampaignPostTypes.
	 */
	public function migrate_campaigns_to_posts(): void {
		foreach ( get_option( '_kudos_campaigns' ) as $campaign ) {

			// Create post and store ID.
			$new_id = wp_insert_post(
				[
					'post_type'   => CampaignPostType::get_slug(),
					'post_title'  => $campaign['name'],
					'post_status' => 'publish',
				]
			);

			// Add post meta to new post.
			update_post_meta( $new_id, CampaignPostType::META_FIELD_INITIAL_TITLE, $campaign['modal_title'] );
			update_post_meta( $new_id, CampaignPostType::META_FIELD_INITIAL_DESCRIPTION, $campaign['welcome_text'] );
			update_post_meta( $new_id, CampaignPostType::META_FIELD_ADDRESS_ENABLED, $campaign['address_enabled'] );
			update_post_meta( $new_id, CampaignPostType::META_FIELD_ADDRESS_REQUIRED, $campaign['address_required'] );
			update_post_meta( $new_id, CampaignPostType::META_FIELD_MESSAGE_ENABLED, $campaign['message_enabled'] );
			update_post_meta( $new_id, CampaignPostType::META_FIELD_AMOUNT_TYPE, $campaign['amount_type'] );
			update_post_meta( $new_id, CampaignPostType::META_FIELD_GOAL, $campaign['campaign_goal'] );
			update_post_meta( $new_id, CampaignPostType::META_FIELD_ADDITIONAL_FUNDS, $campaign['additional_funds'] );
			update_post_meta( $new_id, CampaignPostType::META_FIELD_SHOW_GOAL, $campaign['show_progress'] );
			update_post_meta( $new_id, CampaignPostType::META_FIELD_DONATION_TYPE, $campaign['donation_type'] );

			// Add fixed amounts separately.
			$fixed_amounts = explode( ',', $campaign['fixed_amounts'] );
			foreach ( $fixed_amounts as $fixed_amount ) {
				add_post_meta( $new_id, CampaignPostType::META_FIELD_FIXED_AMOUNTS, $fixed_amount );
			}

			// Store old and new ID for later reference.
			$this->cache['campaign_id'][ $campaign['id'] ] = $new_id;
		}
	}

	/**
	 * Migrate transactions from kudos_transactions table to
	 * TransactionPostTypes.
	 */
	public function migrate_transactions_to_posts(): void {
		global $wpdb;
		$table_name = $wpdb->prefix . 'kudos_transactions';
		$query      = "SELECT * FROM {$table_name}";
		$results    = $wpdb->get_results( $query );

		foreach ( $results as $transaction ) {

			// Create post and store ID.
			$new_id = wp_insert_post(
				[
					'post_type'   => TransactionPostType::get_slug(),
					'post_date'   => $transaction->created,
					'post_status' => 'publish',
				]
			);

			// Add post meta to new post.
			update_post_meta( $new_id, TransactionPostType::META_FIELD_VALUE, $transaction->value );
			update_post_meta( $new_id, TransactionPostType::META_FIELD_CURRENCY, $transaction->currency );
			update_post_meta( $new_id, TransactionPostType::META_FIELD_STATUS, $transaction->status );
			update_post_meta( $new_id, TransactionPostType::META_FIELD_METHOD, $transaction->method );
			update_post_meta( $new_id, TransactionPostType::META_FIELD_MODE, $transaction->mode );
			update_post_meta( $new_id, TransactionPostType::META_FIELD_SEQUENCE_TYPE, $transaction->sequence_type );
			update_post_meta( $new_id, TransactionPostType::META_FIELD_DONOR_ID, $this->cache['donor_id'][ $transaction->customer_id ] );
			update_post_meta( $new_id, TransactionPostType::META_FIELD_VENDOR_PAYMENT_ID, $transaction->transaction_id );
			update_post_meta( $new_id, TransactionPostType::META_FIELD_REFUNDS, $transaction->refunds );
			update_post_meta( $new_id, TransactionPostType::META_FIELD_CAMPAIGN_ID, $this->cache['campaign_id'][ $transaction->campaign_id ] );
			update_post_meta( $new_id, TransactionPostType::META_FIELD_MESSAGE, $transaction->message );
		}
	}
}
