<?php

namespace Kudos\Service;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Settings;

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
	 * @since    1.0.0
	 */
	public static function activate() {

		LoggerService::init();
		TwigService::init();
		self::create_donors_table();
		self::create_transactions_table();
		self::create_subscriptions_table();
		self::set_defaults();

	}

	/**
	 * Creates the donors table
	 *
	 * @since    1.1.0
	 */
	private static function create_donors_table() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = DonorEntity::getTableName();  //get the database table prefix to create my new table

		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		  last_updated datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
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
		$table_name = TransactionEntity::getTableName();  //get the database table prefix to create my new table

		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		  last_updated datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
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
		  donation_label VARCHAR(255),
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
        $table_name = SubscriptionEntity::getTableName();  //get the database table prefix to create my new table

        $sql = "CREATE TABLE $table_name (
		  id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
          created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
          last_updated datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		  value DECIMAL(7,2) NOT NULL,
		  currency VARCHAR(255),
		  frequency VARCHAR(255) NOT NULL,
		  years MEDIUMINT(2) NOT NULL,
		  customer_id VARCHAR(255),
		  transaction_id VARCHAR(255),
		  subscription_id VARCHAR(255),
		  status VARCHAR(255),
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

		update_option('_kudos_donations_version', KUDOS_VERSION);
		Settings::add_defaults();

	}
}
