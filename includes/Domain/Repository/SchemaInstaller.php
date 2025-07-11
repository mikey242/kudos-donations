<?php
/**
 * Schema for tables.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

namespace IseardMedia\Kudos\Domain\Repository;

use IseardMedia\Kudos\Container\ActivationAwareInterface;
use IseardMedia\Kudos\Helper\WpDb;

class SchemaInstaller implements ActivationAwareInterface {
	private WpDb $wpdb;

	public const TABLE_NAMES = [
		CampaignRepository::TABLE_NAME,
		DonorRepository::TABLE_NAME,
		TransactionRepository::TABLE_NAME,
		SubscriptionRepository::TABLE_NAME,
	];

	/**
	 * SchemaInstaller constructor.
	 *
	 * @param WpDb $wpdb The WordPress database class wrapper.
	 */
	public function __construct( WpDb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * {@inheritDoc}
	 */
	public function on_plugin_activation(): void {
		$this->create_schema();
	}

	/**
	 * Creates all table schemas.
	 */
	public function create_schema() {
		$this->create_campaigns_table();
		$this->create_transactions_table();
		$this->create_donors_table();
		$this->create_subscriptions_table();
	}

	/**
	 * Creates the kudos_campaigns custom table.
	 */
	public function create_campaigns_table(): void {
		if ( $this->wpdb->table_exists( CampaignRepository::TABLE_NAME ) ) {
			return;
		}

		$table   = $this->wpdb->table( CampaignRepository::TABLE_NAME );
		$charset = $this->wpdb->get_charset_collate();

		$sql = "
			CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				wp_post_id bigint(20) unsigned DEFAULT NULL,
				wp_post_slug varchar(128) DEFAULT NULL,
				title varchar(255) DEFAULT NULL,
				currency char(3) NOT NULL DEFAULT 'EUR',
				goal decimal(10, 2) DEFAULT NULL,
				show_goal tinyint(1) DEFAULT 0,
				additional_funds decimal(10, 2) DEFAULT NULL,
				amount_type varchar(20) DEFAULT 'fixed',
				fixed_amounts text DEFAULT NULL,
				minimum_donation decimal(10,2) DEFAULT NULL,
				maximum_donation decimal(10,2) DEFAULT NULL,
				donation_type varchar(20) DEFAULT 'oneoff',
				frequency_options text DEFAULT NULL,
				email_enabled tinyint(1) DEFAULT 1,
				email_required tinyint(1) DEFAULT 1,
				name_enabled tinyint(1) DEFAULT 1,
				name_required tinyint(1) DEFAULT 1,
				address_enabled tinyint(1) DEFAULT 0,
				address_required tinyint(1) DEFAULT 0,
				message_enabled tinyint(1) DEFAULT 0,
				message_required tinyint(1) DEFAULT 0,
				theme_color varchar(20) DEFAULT '#ff9f1c',
				terms_link text DEFAULT NULL,
				privacy_link text DEFAULT NULL,
				show_return_message tinyint(1) DEFAULT 0,
				use_custom_return_url tinyint(1) DEFAULT 0,
				custom_return_url text DEFAULT NULL,
				payment_description_format text DEFAULT NULL,
				custom_styles text DEFAULT NULL,
				initial_title text DEFAULT NULL,
				initial_description text DEFAULT NULL,
				subscription_title text DEFAULT NULL,
				subscription_description text DEFAULT NULL,
				address_title text DEFAULT NULL,
				address_description text DEFAULT NULL,
				message_title text DEFAULT NULL,
				message_description text DEFAULT NULL,
				payment_title text DEFAULT NULL,
				payment_description text DEFAULT NULL,
				return_message_title text DEFAULT NULL,
				return_message_text text DEFAULT NULL,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT NULL,
				KEY idx_post (wp_post_id),
				PRIMARY KEY  (id)
			) {$charset};
		";

		$this->wpdb->run_dbdelta( $sql );
	}

	/**
	 * Creates the kudos_transactions custom table.
	 */
	public function create_transactions_table(): void {
		if ( $this->wpdb->table_exists( TransactionRepository::TABLE_NAME ) ) {
			return;
		}

		$table   = $this->wpdb->table( TransactionRepository::TABLE_NAME );
		$charset = $this->wpdb->get_charset_collate();

		$sql = "
			CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				wp_post_id bigint(20) unsigned DEFAULT NULL,
				title varchar(255) DEFAULT NULL,
				value decimal(10,2) NOT NULL,
				currency char(3) NOT NULL DEFAULT 'EUR',
				status varchar(20) NOT NULL,
				method varchar(50),
				mode varchar(20),
				sequence_type varchar(20),
				donor_id bigint(20) unsigned DEFAULT NULL,
				campaign_id bigint(20) unsigned DEFAULT NULL,
				subscription_id bigint(20) unsigned DEFAULT NULL,
				vendor varchar(100),
				vendor_payment_id varchar(255),
				invoice_number bigint(20) unsigned DEFAULT NULL,
				checkout_url text DEFAULT NULL,
				message text DEFAULT NULL,
				refunds text DEFAULT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT NULL,
				KEY idx_status (status),
				KEY idx_campaign (campaign_id),
				KEY idx_donor (donor_id),
				KEY idx_subscription (subscription_id),
				KEY idx_vendor_payment (vendor_payment_id(191)),
				KEY idx_post (wp_post_id),
				PRIMARY KEY  (id)
			) {$charset};
		";

		$this->wpdb->run_dbdelta( $sql );
	}

	/**
	 * Creates the kudos_donors custom table.
	 */
	public function create_donors_table(): void {
		if ( $this->wpdb->table_exists( DonorRepository::TABLE_NAME ) ) {
			return;
		}

		$table   = $this->wpdb->table( DonorRepository::TABLE_NAME );
		$charset = $this->wpdb->get_charset_collate();

		$sql = "
			CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				wp_post_id bigint(20) unsigned DEFAULT NULL,
				title varchar(255) DEFAULT NULL,
				email varchar(255),
				mode varchar(20),
				name varchar(255),
				business_name varchar(255),
				street varchar(255),
				postcode varchar(50),
				city varchar(100),
				country char(2),
				vendor_customer_id varchar(255),
				locale char(5) DEFAULT NULL,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT NULL,
				KEY idx_post (wp_post_id),
				KEY idx_email (email),
				KEY idx_country (country),
               	KEY idx_locale (locale),
               	PRIMARY KEY  (id)
			) {$charset};
		";

		$this->wpdb->run_dbdelta( $sql );
	}

	/**
	 * Creates the kudos_subscriptions custom table.
	 */
	public function create_subscriptions_table(): void {
		if ( $this->wpdb->table_exists( SubscriptionRepository::TABLE_NAME ) ) {
			return;
		}

		$table   = $this->wpdb->table( SubscriptionRepository::TABLE_NAME );
		$charset = $this->wpdb->get_charset_collate();

		$sql = "
			CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				wp_post_id bigint(20) unsigned DEFAULT NULL,
				title varchar(255) DEFAULT NULL,
				value decimal(10,2) NOT NULL,
				currency char(3) NOT NULL DEFAULT 'EUR',
				frequency varchar(50),
				years int DEFAULT NULL,
				status varchar(20),
				transaction_id bigint(20) unsigned DEFAULT NULL,
				donor_id bigint(20) unsigned DEFAULT NULL,
				campaign_id bigint(20) unsigned DEFAULT NULL,
				vendor_customer_id varchar(255),
				vendor_subscription_id varchar(255),
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT NULL,
				KEY idx_post (wp_post_id),
				KEY idx_status (status),
				KEY idx_frequency (frequency),
				KEY idx_transaction (transaction_id),
				KEY idx_donor (donor_id),
				PRIMARY KEY  (id)
			) {$charset};
		";

		$this->wpdb->run_dbdelta( $sql );
	}
}
