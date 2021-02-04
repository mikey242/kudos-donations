<?php

namespace Kudos\Service;

use Kudos\Entity\CampaignEntity;
use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Campaigns;
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
	 * Runs all activation functions.
	 *
	 * @param string|null $old_version Previous version of plugin.
	 *
	 * @since    1.0.0
	 */
	public static function activate( $old_version = null ) {

		$logger = new LoggerService();
		$logger->init();
		$twig = new TwigService();
		$twig->init();

		if ( $old_version ) {
			self::run_migrations($old_version);
		}

		self::create_donors_table();
		self::create_transactions_table();
		self::create_subscriptions_table();
		self::set_defaults();

		$logger->info( 'Kudos Donations plugin activated' );

	}

	/**
	 * Run migrations if upgrading
	 *
	 * @param string $old_version
	 *
	 * @since 2.3.2
	 */
	public static function run_migrations( string $old_version ) {

		if ( version_compare( $old_version, '2.1.1', '<' ) ) {
			Settings::remove_setting( 'action_scheduler' );
		}

		if ( version_compare( $old_version, '2.2.0', '<' ) ) {
			$link = Settings::get_setting( 'privacy_link' );
			Settings::remove_setting( 'subscription_enabled' );

			if ( $link ) {
				Settings::update_setting( 'terms_link', $link );
				Settings::remove_setting( 'privacy_link' );
			}
		}

		if ( version_compare( $old_version, '2.3.0', '<' ) ) {
			global $wpdb;

			// Rename setting
			$transaction_table = TransactionEntity::get_table_name();
			$wpdb->query( "ALTER TABLE $transaction_table RENAME COLUMN `campaign_label` TO `campaign_id`" );
			Settings::update_setting( 'show_intro', 1 );

			// Apply mode to Donors
			$donor_table = DonorEntity::get_table_name();
			$wpdb->query("ALTER TABLE $donor_table ADD `mode` VARCHAR(45) NOT NULL");
			$mapper = new MapperService( DonorEntity::class );
			$donors = $mapper->get_all_by();
			/** @var DonorEntity $donor */
			foreach ( $donors as $donor ) {
				$transactions = $donor->get_transactions();
				if ( $transactions ) {
					$donor->set_fields( [ 'mode' => $transactions[0]->mode ] );
				}
				$mapper->save( $donor );
			}
		}

		if ( version_compare( $old_version, '2.3.2', '<' ) ) {

			// Setting now replaced by 'theme_colors'
			$old_color = Settings::get_setting('theme_color');
			$new_colors = Settings::get_setting('theme_colors');
			$new_colors['primary'] = $old_color;
			Settings::update_setting('theme_colors', $new_colors);
			Settings::remove_setting( 'theme_color' );

		}

	}

	/**
	 * Creates the donors table
	 *
	 * @since    1.1.0
	 */
	private static function create_donors_table() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = DonorEntity::get_table_name();

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
		  mode VARCHAR(45) NOT NULL,
		  PRIMARY KEY (id)
		) $charset_collate";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
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
		$table_name      = TransactionEntity::get_table_name();

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
		  campaign_id VARCHAR(255),
		  secret VARCHAR(255),
		  PRIMARY KEY (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
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
		$table_name      = SubscriptionEntity::get_table_name();

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

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
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

		$campaigns = new Campaigns();
		$campaigns->add_default();

	}

	/**
	 * Creates the subscription table
	 *
	 * @since    1.1.0
	 */
	private static function create_campaigns_table() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = CampaignEntity::get_table_name();

		$sql = "CREATE TABLE $table_name (
	      id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
		  slug VARCHAR(255),
		  name VARCHAR(255),
		  modal_title VARCHAR(255),
		  welcome_text VARCHAR(255),
		  donation_type VARCHAR(255),
		  amount_type VARCHAR(255),
		  fixed_amounts VARCHAR(255),
		  protected BIT,
	      secret VARCHAR(255),
	      PRIMARY KEY (id)
		) $charset_collate";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

	}
}
