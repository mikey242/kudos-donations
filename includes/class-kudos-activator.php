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
		Kudos_Activator::create_transactions_db();
		Kudos_Activator::set_defaults();
	}

	/**
	 * Creates the transactions database
	 *
	 * @since    1.0.0
	 */
	private static function create_transactions_db() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . "kudos_transactions";  //get the database table prefix to create my new table

		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  name VARCHAR(255), 
		  email VARCHAR(320),
		  value DECIMAL(7,2) NOT NULL,
		  status VARCHAR(255) DEFAULT 'open' NOT NULL,
		  method VARCHAR(255),
		  mode VARCHAR(255) NOT NULL, 
		  order_id VARCHAR(255) NOT NULL,
		  transaction_id VARCHAR(255),  
		  PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Adds default options if not already set
	 *
	 * @since    1.0.0
	 */
	private static function set_defaults() {
		if(!get_option('_kudos_mollie_api_mode')) {
			$options = [
				'_kudos_mollie_api_mode' => 'test',
				'_kudos_button_style' => 'kudos_btn_primary',
				'_kudos_button_label' => __('Donate now', 'kudos-donations'),
				'_kudos_form_header' => __('Support us!', 'kudos-donations'),
				'_kudos_form_text' => __('Thank you for your donation. We appreciate your support!', 'kudos-donations'),
				'_kudos_return_message_enable' => 'yes',
				'_kudos_return_message_header' => __('Thank you!', 'kudos-donations'),
				/* translators: %s: Value of donation */
				'_kudos_return_message_text' => sprintf(__('Many thanks for your donation of %s. We appreciate your support.', 'kudos-donations'), '{{value}}')
			];

			foreach ($options as $option=>$value) {
				add_option($option, $value);
			}
		}
	}
}
