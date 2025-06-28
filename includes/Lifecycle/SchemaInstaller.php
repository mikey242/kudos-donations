<?php
/**
 * Schema for tables.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

namespace IseardMedia\Kudos\Lifecycle;

use IseardMedia\Kudos\Container\ActivationAwareInterface;
use IseardMedia\Kudos\Helper\WpDb;
use IseardMedia\Kudos\Repository\CampaignRepository;
use IseardMedia\Kudos\Repository\DonorRepository;
use IseardMedia\Kudos\Repository\SubscriptionRepository;
use IseardMedia\Kudos\Repository\TransactionRepository;

class SchemaInstaller implements ActivationAwareInterface {
	private WpDb $wpdb;

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
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				wp_post_id BIGINT UNSIGNED NULL,
				wp_post_slug VARCHAR(128) NULL,
				title VARCHAR(255) NULL,
				currency CHAR(3) NOT NULL DEFAULT 'EUR',
				goal DECIMAL(10, 2) DEFAULT NULL,
				show_goal BOOLEAN DEFAULT FALSE,
				additional_funds DECIMAL(10, 2) DEFAULT NULL,
				amount_type VARCHAR(20) DEFAULT 'fixed',
				fixed_amounts TEXT DEFAULT NULL,
				minimum_donation DECIMAL(10,2) DEFAULT NULL,
				maximum_donation DECIMAL(10,2) DEFAULT NULL,
				donation_type VARCHAR(20) DEFAULT 'oneoff',
				frequency_options TEXT DEFAULT NULL,
				email_enabled BOOLEAN DEFAULT TRUE,
				email_required BOOLEAN DEFAULT TRUE,
				name_enabled BOOLEAN DEFAULT TRUE,
				name_required BOOLEAN DEFAULT TRUE,
				address_enabled BOOLEAN DEFAULT FALSE,
				address_required BOOLEAN DEFAULT FALSE,
				message_enabled BOOLEAN DEFAULT FALSE,
				message_required BOOLEAN DEFAULT FALSE,
				theme_color VARCHAR(20) DEFAULT '#ff9f1c',
				terms_link TEXT DEFAULT NULL,
				privacy_link TEXT DEFAULT NULL,
				show_return_message BOOLEAN DEFAULT FALSE,
				use_custom_return_url BOOLEAN DEFAULT FALSE,
				custom_return_url TEXT DEFAULT NULL,
				payment_description_format TEXT DEFAULT NULL,
				custom_styles TEXT DEFAULT NULL,
				initial_title TEXT DEFAULT NULL,
				initial_description TEXT DEFAULT NULL,
				subscription_title TEXT DEFAULT NULL,
				subscription_description TEXT DEFAULT NULL,
				address_title TEXT DEFAULT NULL,
				address_description TEXT DEFAULT NULL,
				message_title TEXT DEFAULT NULL,
				message_description TEXT DEFAULT NULL,
				payment_title TEXT DEFAULT NULL,
				payment_description TEXT DEFAULT NULL,
				return_message_title TEXT DEFAULT NULL,
				return_message_text TEXT DEFAULT NULL,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT NULL,
				UNIQUE KEY unique_post (wp_post_id)
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
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				wp_post_id BIGINT UNSIGNED NULL,
				title VARCHAR(255) NULL,
				value DECIMAL(10,2) NOT NULL,
				currency CHAR(3) NOT NULL DEFAULT 'EUR',
				status VARCHAR(20) NOT NULL,
				method VARCHAR(50),
				mode VARCHAR(20),
				sequence_type VARCHAR(20),
				donor_id BIGINT UNSIGNED DEFAULT NULL,
				campaign_id BIGINT UNSIGNED DEFAULT NULL,
				subscription_id BIGINT UNSIGNED DEFAULT NULL,
				vendor VARCHAR(100),
				vendor_payment_id VARCHAR(255),
				invoice_number BIGINT UNSIGNED DEFAULT NULL,
				checkout_url TEXT DEFAULT NULL,
				message TEXT DEFAULT NULL,
				refunds TEXT DEFAULT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT NULL,
				KEY idx_status (status),
				KEY idx_campaign (campaign_id),
				KEY idx_donor (donor_id),
				KEY idx_subscription (subscription_id),
				KEY idx_vendor_payment (vendor_payment_id(191)),
				UNIQUE KEY unique_post (wp_post_id)
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
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				wp_post_id BIGINT UNSIGNED NULL,
				title VARCHAR(255) NULL,
				email VARCHAR(255),
				mode VARCHAR(20),
				name VARCHAR(255),
				business_name VARCHAR(255),
				street VARCHAR(255),
				postcode VARCHAR(50),
				city VARCHAR(100),
				country CHAR(2),
				vendor_customer_id VARCHAR(255),
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT NULL,
				UNIQUE KEY unique_post (wp_post_id),
				KEY idx_email (email),
				KEY idx_country (country)
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
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				wp_post_id BIGINT UNSIGNED NULL,
				title VARCHAR(255) NULL,
				value DECIMAL(10,2) NOT NULL,
				currency CHAR(3) NOT NULL DEFAULT 'EUR',
				frequency VARCHAR(50),
				years INT DEFAULT NULL,
				status VARCHAR(20),
				transaction_id BIGINT UNSIGNED DEFAULT NULL,
				donor_id BIGINT UNSIGNED DEFAULT NULL,
				vendor_customer_id VARCHAR(255),
				vendor_subscription_id VARCHAR(255),
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at DATETIME DEFAULT NULL,
				UNIQUE KEY unique_post (wp_post_id),
				KEY idx_status (status),
				KEY idx_frequency (frequency),
				KEY idx_transaction (transaction_id),
				KEY idx_donor (donor_id)
			) {$charset};
		";

		$this->wpdb->run_dbdelta( $sql );
	}
}
