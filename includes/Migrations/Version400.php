<?php
/**
 * Migration for version 4.0.0.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 *
 * phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
 */

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Domain\PostType\CampaignPostType;
use IseardMedia\Kudos\Domain\PostType\DonorPostType;
use IseardMedia\Kudos\Domain\PostType\SubscriptionPostType;
use IseardMedia\Kudos\Domain\PostType\TransactionPostType;
use IseardMedia\Kudos\Service\InvoiceService;
use IseardMedia\Kudos\Vendor\EmailVendor\SMTPVendor;
use IseardMedia\Kudos\Vendor\PaymentVendor\MolliePaymentVendor;

class Version400 extends BaseMigration {

	/**
	 * {@inheritDoc}
	 */
	public function get_migration_jobs(): array {
		return [
			// 'settings'      => $this->job( [ $this, 'migrate_settings' ], 'Settings' ),
							'campaigns' => $this->job( [ $this, 'migrate_campaigns_to_posts' ], 'Campaigns' ),
			'donors'                    => $this->job( [ $this, 'migrate_donors_to_posts' ], 'Donors', true ),
			'transactions'              => $this->job( [ $this, 'migrate_transactions_to_posts' ], 'Transactions', true ),
			'subscriptions'             => $this->job( [ $this, 'migrate_subscriptions_to_posts' ], 'Subscriptions', true ),
		];
	}

	/**
	 * Migrate all the settings.
	 */
	protected function migrate_settings(): bool {
		$this->migrate_vendor_settings();
		$this->migrate_smtp_settings();
		return true;
	}

	/**
	 * Migrate the old vendor settings.
	 */
	protected function migrate_vendor_settings() {
		$vendor_mollie = get_option( '_kudos_vendor_mollie' );
		$test_key      = $vendor_mollie['test_key'] ?? null;
		$live_key      = $vendor_mollie['live_key'] ?? null;
		$mode          = $vendor_mollie['mode'] ?? 'test';

		update_option( MolliePaymentVendor::SETTING_API_MODE, $mode );

		if ( $live_key ) {
			add_filter( 'kudos_mollie_live_key_validation', '__return_true' );
			update_option( MolliePaymentVendor::SETTING_API_KEY_LIVE, $live_key );
			remove_filter( 'kudos_mollie_live_key_validation', '__return_true' );
		}

		if ( $test_key ) {
			add_filter( 'kudos_mollie_test_key_validation', '__return_true' );
			update_option( MolliePaymentVendor::SETTING_API_KEY_TEST, $test_key );
			remove_filter( 'kudos_mollie_test_key_validation', '__return_true' );
		}
	}

	/**
	 * Migrate custom SMTP config.
	 */
	protected function migrate_smtp_settings() {
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
			update_option( SMTPVendor::SETTING_SMTP_PASSWORD, $password );
		}

		update_option( SMTPVendor::SETTING_CUSTOM_SMTP, $new_settings );
	}

	/**
	 * Migrate campaigns from a settings array to CampaignPostTypes.
	 */
	protected function migrate_campaigns_to_posts(): bool {
		$campaigns = get_option( '_kudos_campaigns', [] );

		// Global settings.
		$theme_colour         = get_option( '_kudos_theme_colors' );
		$return_message_title = get_option( '_kudos_return_message_title' );
		$return_message_text  = get_option( '_kudos_return_message_text' );
		$custom_return_url    = get_option( '_kudos_custom_return_url' );
		$show_return_message  = get_option( '_kudos_completed_payment' ) === 'message';
		$custom_return_enable = get_option( '_kudos_completed_payment' ) === 'url';
		$terms_url            = get_option( '_kudos_terms_link' );
		$privacy_url          = get_option( '_kudos_privacy_link' );

		$total = 0;

		foreach ( $campaigns as $campaign ) {
			$new_campaign = CampaignPostType::save(
				[
					'post_title'                           => $campaign['name'] ?? 'Default',
					'post_name'                            => $campaign['id'],
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
					CampaignPostType::META_FIELD_TERMS_LINK => $terms_url,
					CampaignPostType::META_FIELD_PRIVACY_LINK => $privacy_url,
				]
			);

			// Bail if post not created.
			if ( ! $new_campaign ) {
				return false;
			}

			// Store old and new ID for later reference.
			$mapping                    = get_transient( 'kudos_campaign_id_map' ) ?? [];
			$mapping[ $campaign['id'] ] = $new_campaign->ID;
			set_transient( 'kudos_campaign_id_map', $mapping, DAY_IN_SECONDS );
			++$total;
		}

		return true;
	}

	/**
	 * Migrates donors from custom table to custom post type.
	 *
	 * @param int $limit The number of rows to process.
	 */
	protected function migrate_donors_to_posts( int $limit = self::DEFAULT_CHUNK_SIZE ): bool {
		$table_name = $this->wpdb->prefix . 'kudos_donors';

		// Check table exists.
		if ( ! $this->table_exists( $table_name ) ) {
			return false;
		}

		// Get data.
		$offset_key = 'donors_offset';
		$offset     = $this->progress[ $offset_key ] ?? 0;
		$rows       = $this->get_rows( $table_name, $offset, $limit );

		foreach ( $rows as $donor ) {
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
			// Update transient cache.
			$map                        = get_transient( 'kudos_donor_id_map' ) ?? [];
			$map[ $donor->customer_id ] = $new_donor->ID;
			set_transient( 'kudos_donor_id_map', $map, DAY_IN_SECONDS );
		}

		// Update progress.
		$this->progress[ $offset_key ] = $offset + \count( $rows );
		$this->update_progress();

		return \count( $rows ) < $limit;
	}

	/**
	 * Migrate transactions from kudos_transactions table to
	 * TransactionPostTypes.
	 *
	 * @param int $limit The number of rows to process.
	 */
	protected function migrate_transactions_to_posts( int $limit = self::DEFAULT_CHUNK_SIZE ): bool {
		$table_name = $this->wpdb->prefix . 'kudos_transactions';

		// Check table exists.
		if ( ! $this->table_exists( $table_name ) ) {
			return false;
		}

		// Get cache.
		$donor_cache    = get_transient( 'kudos_donor_id_map' );
		$campaign_cache = get_transient( 'kudos_campaign_id_map' );

		// Get data.
		$invoice_number = (int) get_option( InvoiceService::SETTING_INVOICE_NUMBER, 1 );
		$offset_key     = 'transactions_offset';
		$offset         = $this->progress[ $offset_key ] ?? 0;
		$rows           = $this->get_rows( $table_name, $offset, $limit );

		foreach ( $rows as $transaction ) {
			$new_transaction = TransactionPostType::save(
				[
					'post_date'                            => $transaction->created,
					TransactionPostType::META_FIELD_VALUE  => (int) $transaction->value,
					TransactionPostType::META_FIELD_CURRENCY => $transaction->currency,
					TransactionPostType::META_FIELD_STATUS => $transaction->status,
					TransactionPostType::META_FIELD_METHOD => $transaction->method,
					TransactionPostType::META_FIELD_MODE   => $transaction->mode,
					TransactionPostType::META_FIELD_SEQUENCE_TYPE => $transaction->sequence_type,
					TransactionPostType::META_FIELD_DONOR_ID => $donor_cache[ $transaction->customer_id ],
					TransactionPostType::META_FIELD_VENDOR_PAYMENT_ID => $transaction->transaction_id,
					TransactionPostType::META_FIELD_REFUNDS => $transaction->refunds,
					TransactionPostType::META_FIELD_CAMPAIGN_ID => $campaign_cache[ $transaction->campaign_id ],
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
			}

			// Store old and new ID for later reference.
			$mapping                                 = get_transient( 'kudos_transaction_id_map' ) ?? [];
			$mapping[ $transaction->transaction_id ] = $new_transaction->ID;
			set_transient( 'kudos_transaction_id_map', $mapping, DAY_IN_SECONDS );
		}

		update_option( InvoiceService::SETTING_INVOICE_NUMBER, $invoice_number );

		// Update progress.
		$this->progress[ $offset_key ] = $offset + \count( $rows );
		$this->update_progress();

		return \count( $rows ) < $limit;
	}

	/**
	 * Migrate transactions from kudos_transactions table to
	 * TransactionPostTypes.
	 *
	 * @param int $limit The number of rows to process.
	 */
	protected function migrate_subscriptions_to_posts( int $limit = self::DEFAULT_CHUNK_SIZE ): bool {
		$table_name = $this->wpdb->prefix . 'kudos_subscriptions';

		// Check table exists.
		if ( ! $this->table_exists( $table_name ) ) {
			return false;
		}

		// Cache.
		$transaction_cache = get_transient( 'kudos_transaction_id_map' ) ?? [];

		// Fetch data.
		$offset_key = 'subscriptions_offset';
		$offset     = $this->progress[ $offset_key ] ?? 0;
		$rows       = $this->get_rows( $table_name, $offset, $limit );

		foreach ( $rows as $subscription ) {
			$new_subscription = SubscriptionPostType::save(
				[
					'post_date'                            => $subscription->created,
					SubscriptionPostType::META_FIELD_VALUE => (int) $subscription->value,
					SubscriptionPostType::META_FIELD_CURRENCY => (string) $subscription->currency,
					SubscriptionPostType::META_FIELD_FREQUENCY => (string) $subscription->frequency,
					SubscriptionPostType::META_FIELD_YEARS => (int) $subscription->years,
					SubscriptionPostType::META_FIELD_CUSTOMER_ID => (string) $subscription->customer_id,
					SubscriptionPostType::META_FIELD_TRANSACTION_ID => (string) $transaction_cache[ $subscription->transaction_id ],
					SubscriptionPostType::META_FIELD_VENDOR_SUBSCRIPTION_ID => (string) $subscription->subscription_id,
					SubscriptionPostType::META_FIELD_STATUS => (string) $subscription->status,
				]
			);

			// Bail if post not created.
			if ( ! $new_subscription ) {
				return false;
			}
		}

		// Update progress.
		$this->progress[ $offset_key ] = $offset + \count( $rows );
		$this->update_progress();

		return \count( $rows ) < $limit;
	}

	/**
	 * Check if specified table exists.
	 *
	 * @param string $table_name The table name to check (e.g. wp_kudos_transactions).
	 */
	private function table_exists( string $table_name ): bool {
		// Check table exists.
		$check = $this->wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name );
		if ( $this->wpdb->get_var( $check ) !== $table_name ) {
			$this->logger->error( 'Table not found for migration step', [ 'table' => $table_name ] );
			return false;
		}
		return true;
	}

	/**
	 * Fetch records.
	 *
	 * @param string $table_name Table name to query.
	 * @param int    $offset Offset of results.
	 * @param int    $limit Limit of results.
	 * @return array|object|null
	 */
	private function get_rows( string $table_name, int $offset, int $limit ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $this->wpdb->prepare( "SELECT * FROM $table_name LIMIT %d OFFSET %d", $limit, $offset );
		return $this->wpdb->get_results( $query );
	}
}
