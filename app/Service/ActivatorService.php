<?php

namespace Kudos\Service;

use Kudos\Controller\Admin;
use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;
use Kudos\Helpers\WpDb;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class ActivatorService {

	/**
	 * @var LoggerService
	 */
	private $logger;
	/**
	 * @var TwigService
	 */
	private $twig;
	/**
	 * @var WpDb|\wpdb
	 */
	private $wpdb;

	public function __construct() {

		$this->wpdb   = new WpDb();
		$this->logger = new LoggerService();
		$this->twig   = new TwigService( $this->logger );

	}

	/**
	 * Runs all activation functions.
	 *
	 * @param string|null $old_version Previous version of plugin.
	 */
	public function activate( string $old_version = null ) {

		self::create_log_table();
		self::create_donors_table();
		self::create_transactions_table();
		self::create_subscriptions_table();

		$settings = Admin::get_settings();
		$logger   = $this->logger;
		$twig     = $this->twig;
		$twig->init();

		Settings::register_settings( $settings );

		if ( $old_version ) {
			self::run_migrations( $old_version );
		}

		Settings::add_defaults( $settings );

		update_option( '_kudos_donations_version', KUDOS_VERSION );
		$logger->info( 'Kudos Donations plugin activated.', [ 'version' => KUDOS_VERSION ] );

	}

	/**
	 * Run migrations if upgrading.
	 *
	 * @param string $old_version
	 */
	private function run_migrations( string $old_version ) {

		$logger = $this->logger;
		$wpdb   = $this->wpdb;

		$logger->info(
			'Upgrade detected, running migrations.',
			[
				'old_version' => $old_version,
				'new_version' => KUDOS_VERSION,
			] 
		);

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

		if ( version_compare( $old_version, '2.3.2', '<' ) ) {
			// Setting now replaced by 'theme_colors'
			$old_color             = Settings::get_setting( 'theme_color' );
			$new_colors            = Settings::get_setting( 'theme_colors' );
			$new_colors['primary'] = $old_color;
			Settings::update_setting( 'theme_colors', $new_colors );
			Settings::remove_setting( 'theme_color' );
		}

		if ( version_compare( $old_version, '2.3.7', '<' ) ) {
			// Change business_name to allow NULL
			$donor_table = DonorEntity::get_table_name();
			$wpdb->query( "ALTER TABLE $donor_table MODIFY `business_name` VARCHAR(255)" );
		}

		if ( version_compare( $old_version, '2.4.0', '<' ) ) {
			// Setting now replaced by single 'vendor_mollie' setting.
			$connected = Settings::get_setting( 'mollie_connected' );
			Settings::update_array(
				'vendor_mollie',
				[
					'connected' => (bool) $connected,
					'mode'      => ! empty( Settings::get_setting( 'mollie_api_mode' ) ) ? (string) Settings::get_setting( 'mollie_api_mode' ) : 'test',
					'test_key'  => (string) Settings::get_setting( 'mollie_test_api_key' ),
					'live_key'  => (string) Settings::get_setting( 'mollie_live_api_key' ),
				] 
			);

			// Remove old settings fields.
			Settings::remove_setting( 'mollie_connected' );
			Settings::remove_setting( 'mollie_api_mode' );
			Settings::remove_setting( 'mollie_test_api_key' );
			Settings::remove_setting( 'mollie_live_api_key' );
			Settings::remove_setting( 'campaign_labels' );
		}

		if ( version_compare( $old_version, '2.4.1', '<' ) ) {
			// Cast connected variable as boolean.
			$vendor_settings = Settings::get_setting( 'vendor_mollie' );
			$connected       = ! empty( $vendor_settings['connected'] ) && $vendor_settings['connected'];
			Settings::update_array(
				'vendor_mollie',
				[
					'connected' => $connected,
				] 
			);
		}

		if ( version_compare( $old_version, '2.5.0', '<' ) ) {
			// Add message field to transactions.
			$transaction_table = TransactionEntity::get_table_name();
			$wpdb->query( "ALTER TABLE $transaction_table ADD `message` VARCHAR(255)" );

			// Remove unused settings.
			Settings::remove_setting( 'address_enabled' );
			Settings::remove_setting( 'address_required' );
		}

		if ( version_compare( $old_version, '3.1.0', '<' ) ) {
			// Remove unused settings.
			Settings::remove_setting( 'return_message_enable' );
			Settings::remove_setting( 'custom_return_enable' );

			// Disable log file clearing
			as_unschedule_all_actions( 'kudos_check_log' );
		}

		if ( version_compare( $old_version, '3.1.1', '<' ) ) {
			// Remove 'secret' column from entities.
			$donor_table = DonorEntity::get_table_name();
			$this->wpdb->query( "ALTER TABLE $donor_table DROP COLUMN `secret`" );
			$transaction_table = TransactionEntity::get_table_name();
			$this->wpdb->query( "ALTER TABLE $transaction_table DROP COLUMN `secret`" );
			$subscription_table = SubscriptionEntity::get_table_name();
			$this->wpdb->query( "ALTER TABLE $subscription_table DROP COLUMN `secret`" );
		}

	}

	/**
	 * Creates the donors table.
	 */
	private function create_donors_table() {

		$wpdb = $this->wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = DonorEntity::get_table_name();

		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  last_updated datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  email VARCHAR(320) NOT NULL,
		  name VARCHAR(255) NOT NULL,
		  business_name VARCHAR(255),
		  street VARCHAR(255),
		  postcode VARCHAR(255),
		  city VARCHAR(255),
		  country VARCHAR(255),
		  customer_id VARCHAR(255),
		  mode VARCHAR(45) NOT NULL,
		  PRIMARY KEY (id)
		) $charset_collate";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

	}

	/**
	 * Creates the transactions table.
	 */
	private function create_transactions_table() {

		$wpdb = $this->wpdb;

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
		  message VARCHAR(255),
		  PRIMARY KEY (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

	}

	/**
	 * Creates the subscription table.
	 */
	private function create_subscriptions_table() {

		$wpdb = $this->wpdb;

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
		  PRIMARY KEY (id)
		) $charset_collate";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

	}

	/**
	 * Creates the subscription table.
	 */
	private function create_log_table() {

		$wpdb = $this->wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $this->logger->get_table_name();

		$sql = "CREATE TABLE $table_name (
		  id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
          date DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  level VARCHAR(255) NOT NULL,
		  message TEXT NOT NULL,
		  context TEXT,	  
		  PRIMARY KEY (id)
		) $charset_collate";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

	}
}
