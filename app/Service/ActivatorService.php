<?php

namespace Kudos\Service;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;

/**
 * Fired during plugin activation
 *
 * @link       https://www.linkedin.com/in/michael-iseard/
 * @since      1.0.0
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 */
class ActivatorService {

	/**
	 * Runs all activation functions
	 *
	 * @param $old_version
	 *
	 * @since    1.0.0
	 */
	public static function activate( $old_version = null ) {

		$logger = LoggerService::factory();

		$logger::init();
		TwigService::initCache();
		self::create_donors_table();
		self::create_transactions_table();
		self::create_subscriptions_table();
		self::set_defaults();

		if ( $old_version && version_compare( $old_version, '2.0.4', '<' ) ) {
			$logger->info( 'Upgrading to version 2.0.4', [ 'previous_version' => $old_version ] );
			$result = UpdateService::sync_campaign_labels();
			if ( $result ) {
				$logger->info( 'Updated campaign labels from transactions' );
			}
		}

		$logger->info( 'Kudos Donations plugin activated' );

	}

	/**
	 * Creates the donors table
	 *
	 * @since    1.1.0
	 */
	private static function create_donors_table() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = DonorEntity::get_table_name();  //get the database table prefix to create my new table

		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  last_updated datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  email VARCHAR(320) NOT NULL,
		  name VARCHAR(255) NOT NULL,
		  street VARCHAR(255),
		  postcode VARCHAR(255),
		  city VARCHAR(255),
		  country VARCHAR(255),
		  customer_id VARCHAR(255),
		  secret VARCHAR(255),
		  PRIMARY KEY (id)
		) $charset_collate";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

	}

	/**
	 * Creates the transactions table
	 *
	 * @since    1.0.0
	 */
	private static function create_transactions_table() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = TransactionEntity::get_table_name();  //get the database table prefix to create my new table

		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  last_updated datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  value DECIMAL(7,2) NOT NULL,
		  currency VARCHAR(255),
		  status VARCHAR(255) DEFAULT 'open' NOT NULL,
		  method VARCHAR(255),
		  mode VARCHAR(255) NOT NULL,
		  sequence_type VARCHAR(255) NOT NULL,
		  customer_id varchar(255) NOT NULL,
		  order_id VARCHAR(255) NOT NULL,
		  transaction_id VARCHAR(255),
		  subscription_id VARCHAR(255),
		  refunds BLOB DEFAULT NULL,
		  campaign_label VARCHAR(255),
		  secret VARCHAR(255),
		  PRIMARY KEY (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

	}

	/**
	 * Creates the subscription table
	 *
	 * @since    1.1.0
	 */
	private static function create_subscriptions_table() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = SubscriptionEntity::get_table_name();  //get the database table prefix to create my new table

		$sql = "CREATE TABLE $table_name (
		  id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
          created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
          last_updated datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  value DECIMAL(7,2) NOT NULL,
		  currency VARCHAR(255),
		  frequency VARCHAR(255) NOT NULL,
		  years MEDIUMINT(2) NOT NULL,
		  customer_id VARCHAR(255),
		  transaction_id VARCHAR(255),
		  subscription_id VARCHAR(255),
		  status VARCHAR(255),
		  secret VARCHAR(255),		  
		  PRIMARY KEY (id)
		) $charset_collate";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

	}

	/**
	 * Adds default options if not already set
	 *
	 * @since    1.0.0
	 */
	private static function set_defaults() {

		update_option( '_kudos_donations_version', KUDOS_VERSION );

		$settings = new Settings();
		$settings->add_defaults();

	}
}
