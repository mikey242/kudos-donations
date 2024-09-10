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
use IseardMedia\Kudos\Service\InvoiceService;
use IseardMedia\Kudos\Service\MailerService;
use IseardMedia\Kudos\Vendor\MollieVendor;

class Version400 extends AbstractMigration {

	private array $cache;

	/**
	 * {@inheritDoc}
	 */
	public function run(): bool {
		return (
			$this->migrate_vendor_settings() &&
			$this->migrate_smtp_settings() &&
			$this->migrate_donors_to_posts() &&
			$this->migrate_campaigns_to_posts() &&
			$this->migrate_transactions_to_posts()
		);
	}

	/**
	 * Migrate the old vendor settings.
	 */
	private function migrate_vendor_settings(): bool {
		$vendor_mollie = get_option( '_kudos_vendor_mollie' );
		$test_key      = $vendor_mollie['test_key'] ?? null;
		$live_key      = $vendor_mollie['live_key'] ?? null;
		$mode          = $vendor_mollie['mode'] ?? 'test';

		update_option( MollieVendor::SETTING_API_MODE, $mode );

		if ( $live_key ) {
			add_filter( 'kudos_mollie_live_key_validation', '__return_true' );
			update_option( MollieVendor::SETTING_API_KEY_LIVE, $live_key );
			remove_filter( 'kudos_mollie_live_key_validation', '__return_true' );
		}

		if ( $test_key ) {
			add_filter( 'kudos_mollie_test_key_validation', '__return_true' );
			update_option( MollieVendor::SETTING_API_KEY_TEST, $test_key );
			remove_filter( 'kudos_mollie_test_key_validation', '__return_true' );
		}

		// Always return true, a failure here is not critical.
		return true;
	}

	/**
	 * Migrate custom SMTP config.
	 */
	private function migrate_smtp_settings(): bool {
		$host       = get_option( '_kudos_smtp_host' ) ?? null;
		$port       = get_option( '_kudos_smtp_port' ) ?? null;
		$encryption = get_option( '_kudos_smtp_encryption' ) ?? null;
		$autotls    = get_option( '_kudos_smtp_autotls' ) ?? null;
		$username   = get_option( '_kudos_smtp_username' ) ?? null;
		$password   = get_option( '_kudos_smtp_password' ) ?? null;
		$from_email = get_option( '_kudos_smtp_from' ) ? get_option( '_kudos_smtp_from' ) : $username;

		$new_settings = [];

		$new_settings['from_name'] = get_bloginfo( 'name' );

		if ( $host ) {
			$new_settings['host'] = $host;
		}
		if ( $port ) {
			$new_settings['port'] = $port;
		}
		if ( $encryption ) {
			$new_settings['encryption'] = $encryption;
		}
		if ( $autotls ) {
			$new_settings['autotls'] = $autotls;
		}
		if ( $from_email ) {
			$new_settings['from_email'] = $from_email;
		}
		if ( $username ) {
			$new_settings['username'] = $username;
		}
		if ( $password ) {
			update_option( MailerService::SETTING_SMTP_PASSWORD, $password );
		}

		update_option( MailerService::SETTING_CUSTOM_SMTP, $new_settings );

		// Always return true, a failure here is not critical.
		return true;
	}

	/**
	 * Migrates donors from custom table to custom post type.
	 */
	private function migrate_donors_to_posts(): bool {
		$table_name = $this->wpdb->prefix . 'kudos_donors';

		// Check if table exists.
		$prepare = $this->wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name );
		if ( $this->wpdb->get_var( $prepare ) !== $table_name ) {
			$this->logger->error( 'Donors table not found', [ 'table_name' => $table_name ] );
			return false;
		}

		$query   = "SELECT * FROM $table_name";
		$results = $this->wpdb->get_results( $query );

		foreach ( $results as $donor ) {

			$new_donor = DonorPostType::save(
				[
					'post_date'                        => $donor->created,
					DonorPostType::META_FIELD_EMAIL    => $donor->email,
					DonorPostType::META_FIELD_NAME     => $donor->name,
					DonorPostType::META_FIELD_BUSINESS_NAME => $donor->business_name,
					DonorPostType::META_FIELD_STREET   => $donor->street,
					DonorPostType::META_FIELD_POSTCODE => $donor->postcode,
					DonorPostType::META_FIELD_CITY     => $donor->city,
					DonorPostType::META_FIELD_COUNTRY  => $donor->country,
					DonorPostType::META_FIELD_MODE     => $donor->mode,
					DonorPostType::META_FIELD_VENDOR_CUSTOMER_ID => $donor->customer_id,
				]
			);

			if ( ! $new_donor ) {
				return false;
			}

			$this->logger->debug( 'Donor created', [ 'post_id' => $new_donor->ID ] );

			$this->cache['donor_id'][ $donor->customer_id ] = $new_donor->ID;
		}
		return true;
	}

	/**
	 * Migrate campaigns from a settings array to CampaignPostTypes.
	 */
	private function migrate_campaigns_to_posts(): bool {
		$campaigns = get_option( '_kudos_campaigns', [] );

		// Global settings.
		$theme_colour         = get_option( '_kudos_theme_colors' );
		$return_message_title = get_option( '_kudos_return_message_title' );
		$return_message_text  = get_option( '_kudos_return_message_text' );
		$custom_return_url    = get_option( '_kudos_custom_return_url' );
		$show_return_message  = get_option( '_kudos_completed_payment' ) === 'message';
		$custom_return_enable = get_option( '_kudos_completed_payment' ) === 'url';

		foreach ( $campaigns as $campaign ) {

			$new_campaign = CampaignPostType::save(
				[
					'post_title'                           => $campaign['name'],
					CampaignPostType::META_FIELD_INITIAL_TITLE => $campaign['modal_title'] ?? '',
					CampaignPostType::META_FIELD_INITIAL_DESCRIPTION => $campaign['welcome_text'] ?? '',
					CampaignPostType::META_FIELD_ADDRESS_ENABLED => $campaign['address_enabled'] ?? false,
					CampaignPostType::META_FIELD_ADDRESS_REQUIRED => $campaign['address_required'] ?? false,
					CampaignPostType::META_FIELD_MESSAGE_ENABLED => $campaign['message_enabled'] ?? false,
					CampaignPostType::META_FIELD_AMOUNT_TYPE => $campaign['amount_type'] ?? 'open',
					CampaignPostType::META_FIELD_GOAL      => $campaign['campaign_goal'] ?? '',
					CampaignPostType::META_FIELD_ADDITIONAL_FUNDS => $campaign['additional_funds'] ?? '',
					CampaignPostType::META_FIELD_SHOW_GOAL => $campaign['show_progress'] ?? false,
					CampaignPostType::META_FIELD_DONATION_TYPE => $campaign['donation_type'] ?? 'oneoff',
					CampaignPostType::META_FIELD_FIXED_AMOUNTS => explode( ',', $campaign['fixed_amounts'] ?? '' ) ?? [ 5,10,25,50 ],
					CampaignPostType::META_FIELD_THEME_COLOR => $theme_colour ? $theme_colour['primary'] : '#ff9f1c',
					// Add these global settings which are now campaign scoped.
					CampaignPostType::META_FIELD_SHOW_RETURN_MESSAGE => $show_return_message,
					CampaignPostType::META_FIELD_RETURN_MESSAGE_TITLE => $return_message_title,
					CampaignPostType::META_FIELD_RETURN_MESSAGE_TEXT => $return_message_text,
					CampaignPostType::META_FIELD_USE_CUSTOM_RETURN_URL => $custom_return_enable,
					CampaignPostType::META_FIELD_CUSTOM_RETURN_URL => $custom_return_url,
				]
			);

			// Bail if post not created.
			if ( ! $new_campaign ) {
				return false;
			}

			$this->logger->debug( 'Campaign created', [ 'post_id' => $new_campaign->ID ] );

			// Store old and new ID for later reference.
			$this->cache['campaign_id'][ $campaign['id'] ] = $new_campaign->ID;
		}

		// Campaigns migrated, let's delete the old option.
		delete_option( '_kudos_campaigns' );
		return true;
	}

	/**
	 * Migrate transactions from kudos_transactions table to
	 * TransactionPostTypes.
	 */
	private function migrate_transactions_to_posts(): bool {
		$table_name = $this->wpdb->prefix . 'kudos_transactions';

		// Check if table exists.
		$prepare = $this->wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name );
		if ( $this->wpdb->get_var( $prepare ) !== $table_name ) {
			$this->logger->error( 'Transactions table not found', [ 'table_name' => $table_name ] );
			return false;
		}

		$query          = "SELECT * FROM $table_name";
		$results        = $this->wpdb->get_results( $query );
		$invoice_number = 1;

		foreach ( $results as $transaction ) {

			$new_transaction = TransactionPostType::save(
				[
					'post_date'                            => $transaction->created,
					TransactionPostType::META_FIELD_VALUE  => (int) $transaction->value,
					TransactionPostType::META_FIELD_CURRENCY => $transaction->currency,
					TransactionPostType::META_FIELD_STATUS => $transaction->status,
					TransactionPostType::META_FIELD_METHOD => $transaction->method,
					TransactionPostType::META_FIELD_MODE   => $transaction->mode,
					TransactionPostType::META_FIELD_SEQUENCE_TYPE => $transaction->sequence_type,
					TransactionPostType::META_FIELD_DONOR_ID => $this->cache['donor_id'][ $transaction->customer_id ],
					TransactionPostType::META_FIELD_VENDOR_PAYMENT_ID => $transaction->transaction_id,
					TransactionPostType::META_FIELD_REFUNDS => $transaction->refunds,
					TransactionPostType::META_FIELD_CAMPAIGN_ID => $this->cache['campaign_id'][ $transaction->campaign_id ],
					TransactionPostType::META_FIELD_MESSAGE => $transaction->message,
				]
			);

			// Bail if post not created.
			if ( ! $new_transaction ) {
				return false;
			}

			// If transaction is paid then add invoice number and iterate.
			if ( 'paid' === $transaction->status ) {
				TransactionPostType::save(
					[
						'ID' => $new_transaction->ID,
						TransactionPostType::META_FIELD_INVOICE_NUMBER => $invoice_number,
					]
				);
				++$invoice_number;
			}

			$this->logger->debug( 'Transaction created', [ 'post_id' => $new_transaction->ID ] );
		}
		update_option( InvoiceService::SETTING_INVOICE_NUMBER, $invoice_number );
		return true;
	}
}
