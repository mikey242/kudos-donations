<?php

namespace Kudos;

/**
 * Fired during plugin activation
 *
 * @link       https://www.linkedin.com/in/michael-iseard/
 * @since      1.0.0
 *
 * @package    Kudos-Donations
 * @subpackage Kudos/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Kudos-Donations
 * @subpackage Kudos/includes
 * @author     Michael Iseard <michael@iseard.media>
 */
class Kudos_Activator {

	/**
	 * Runs all activation functions
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		Kudos_Logger::init();
		self::create_transactions_table();
		self::create_donors_table();
		self::create_subscriptions_table();
		self::set_defaults();
	}

	/**
	 * Creates the transactions table
	 *
	 * @since    1.0.0
	 */
	private static function create_transactions_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = Kudos_Transaction::getTableName();  //get the database table prefix to create my new table

		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  transaction_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  value DECIMAL(7,2) NOT NULL,
		  currency VARCHAR(255),
		  status VARCHAR(255) DEFAULT 'open' NOT NULL,
		  method VARCHAR(255),
		  mode VARCHAR(255) NOT NULL,
		  sequence_type VARCHAR(255) NOT NULL,
		  customer_id VARCHAR(255),
		  order_id VARCHAR(255) NOT NULL,
		  transaction_id VARCHAR(255),
		  subscription_id VARCHAR(255),
		  donation_label VARCHAR(255),
		  PRIMARY KEY (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Creates the donors table
	 *
	 * @since    1.1.0
	 */
	private static function create_donors_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = Kudos_Donor::getTableName();  //get the database table prefix to create my new table

		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  donor_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  email VARCHAR(320) NOT NULL,
		  name VARCHAR(255) NOT NULL,
		  street VARCHAR(255),
		  postcode VARCHAR(255),
		  city VARCHAR(255),
		  country VARCHAR(255),
		  customer_id VARCHAR(255),
		  PRIMARY KEY (id)
		) $charset_collate";

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
        $table_name = Kudos_Subscription::getTableName();  //get the database table prefix to create my new table

        $sql = "CREATE TABLE $table_name (
		  id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
          subscription_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  value DECIMAL(7,2) NOT NULL,
		  currency VARCHAR(255),
		  frequency VARCHAR(255) NOT NULL,
		  years MEDIUMINT(2) NOT NULL,
		  customer_id VARCHAR(255),
		  transaction_id VARCHAR(255),
		  k_subscription_id VARCHAR(255),
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

		update_option('kudos_donations_version', KUDOS_VERSION);

//		--- FOR FUTURE USE ---

//		if(!get_option('_kudos_mollie_api_mode')) {
//			$options = [
//				'_kudos_mollie_api_mode' => 'test',
//				'_kudos_button_style' => 'kudos_btn_primary',
//				'_kudos_button_label' => __('Donate now', 'kudos-donations'),
//				'_kudos_form_header' => __('Support us!', 'kudos-donations'),
//				'_kudos_form_text' => __('Thank you for your donation. We appreciate your support!', 'kudos-donations'),
//				'_kudos_return_message_enable' => 'yes',
//				'_kudos_return_message_header' => __('Thank you!', 'kudos-donations'),
//				/* translators: %s: Value of donation */
//				'_kudos_return_message_text' => sprintf(__('Many thanks for your donation of %s. We appreciate your support.', 'kudos-donations'), '{{value}}')
//			];
//
//			foreach ($options as $option=>$value) {
//				add_option($option, $value);
//			}
//		}
	}
}
