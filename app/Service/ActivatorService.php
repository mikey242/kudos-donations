<?php

namespace Kudos\Service;

use Kudos\Entity\DonorEntity;
use Kudos\Entity\SubscriptionEntity;
use Kudos\Entity\TransactionEntity;
use Kudos\Helpers\Settings;
use Kudos\Helpers\Utils;
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
	 * @var MapperService
	 */
	private $mapper;
	/**
	 * @var WpDb|\wpdb
	 */
	private $wpdb;
	/**
	 * @var Settings
	 */
	private $settings;

	public function __construct() {

		$this->logger = new LoggerService();
		$this->wpdb = new WpDb();
		$this->twig = new TwigService($this->logger);
		$this->mapper = new MapperService($this->logger, $this->wpdb);
		$this->settings = new Settings($this->mapper);

	}

	/**
	 * Runs all activation functions.
	 *
	 * @param string|null $old_version Previous version of plugin.
	 */
	public function activate( string $old_version = null ) {

		$logger = $this->logger;
		$logger->init();
		$twig = $this->twig;
		$twig->init();
		$settings = $this->settings;
		$settings->register_settings();

		if ( $old_version ) {
			self::run_migrations( $old_version );
		}

		$settings->add_defaults();
		self::create_donors_table();
		self::create_transactions_table();
		self::create_subscriptions_table();

		update_option( '_kudos_donations_version', KUDOS_VERSION );
		$logger->info( 'Kudos Donations plugin activated', ['version' => KUDOS_VERSION] );

		// Schedule log file clearing.
		Utils::schedule_recurring_action( strtotime( 'today midnight' ), DAY_IN_SECONDS, 'kudos_clear_log' );

	}

	/**
	 * Run migrations if upgrading
	 *
	 * @param string $old_version
	 */
	private function run_migrations( string $old_version ) {

		$logger = $this->logger;
		$wpdb = $this->wpdb;
		$settings = $this->settings;

		$logger->info( 'Upgrade detected, running migrations.',
			[ 'old_version' => $old_version, 'new_version' => KUDOS_VERSION ] );

		if ( version_compare( $old_version, '2.1.1', '<' ) ) {
			$settings::remove_setting( 'action_scheduler' );
		}

		if ( version_compare( $old_version, '2.2.0', '<' ) ) {
			$link = $settings::get_setting( 'privacy_link' );
			$settings::remove_setting( 'subscription_enabled' );

			if ( $link ) {
				$settings::update_setting( 'terms_link', $link );
				$settings::remove_setting( 'privacy_link' );
			}
		}

		if ( version_compare( $old_version, '2.3.0', '<' ) ) {

			// Rename setting
			$transaction_table = TransactionEntity::get_table_name();
			$wpdb->query( "ALTER TABLE $transaction_table RENAME COLUMN `campaign_label` TO `campaign_id`" );
			$settings::update_setting( 'show_intro', 1 );

			// Apply mode to Donors
			$donor_table = DonorEntity::get_table_name();
			$wpdb->query( "ALTER TABLE $donor_table ADD `mode` VARCHAR(45) NOT NULL" );
			$donors = $this->mapper
				->get_repository(DonorEntity::class)
				->get_all_by();
			/** @var DonorEntity $donor */
			foreach ( $donors as $donor ) {
				$transactions = $donor->get_transactions();
				if ( $transactions ) {
					$donor->set_fields( [ 'mode' => $transactions[0]->mode ] );
				}
				$this->mapper->save( $donor );
			}
		}

		if ( version_compare( $old_version, '2.3.2', '<' ) ) {
			// Setting now replaced by 'theme_colors'
			$old_color             = $settings::get_setting( 'theme_color' );
			$new_colors            = $settings::get_setting( 'theme_colors' );
			$new_colors['primary'] = $old_color;
			$settings::update_setting( 'theme_colors', $new_colors );
			$settings::remove_setting( 'theme_color' );
		}

		if ( version_compare( $old_version, '2.3.7', '<' ) ) {
			// Change business_name to allow NULL
			$donor_table = DonorEntity::get_table_name();
			$wpdb->query( "ALTER TABLE $donor_table MODIFY `business_name` VARCHAR(255)" );
		}

		if ( version_compare( $old_version, '2.4.0', '<' ) ) {
			// Setting now replaced by single 'vendor_mollie' setting.
			$connected = $settings::get_setting( 'mollie_connected' );
			$settings::update_array( 'vendor_mollie',
				[
					'connected' => (bool) $connected,
					'mode'      => ! empty( $settings::get_setting( 'mollie_api_mode' ) ) ? (string) $settings::get_setting( 'mollie_api_mode' ) : 'test',
					'test_key'  => (string) $settings::get_setting( 'mollie_test_api_key' ),
					'live_key'  => (string) $settings::get_setting( 'mollie_live_api_key' ),
				] );

			// Remove old settings fields.
			$settings::remove_setting( 'mollie_connected' );
			$settings::remove_setting( 'mollie_api_mode' );
			$settings::remove_setting( 'mollie_test_api_key' );
			$settings::remove_setting( 'mollie_live_api_key' );
			$settings::remove_setting( 'campaign_labels' );
		}

		if ( version_compare( $old_version, '2.4.1', '<' ) ) {
			// Cast connected variable as boolean.
			$vendor_settings = $settings::get_setting('vendor_mollie');
			$connected = ! empty( $vendor_settings['connected'] ) && $vendor_settings['connected'];
			$settings::update_array('vendor_mollie', [
				'connected' => $connected
			]);
		}

		if ( version_compare( $old_version, '2.5.0', '<' ) ) {
			// Add message field to transactions.
			$transaction_table = TransactionEntity::get_table_name();
			$wpdb->query( "ALTER TABLE $transaction_table ADD `message` VARCHAR(255)" );

			// Remove unused settings.
			$settings::remove_setting('address_enabled');
			$settings::remove_setting('address_required');
		}

	}

	/**
	 * Creates the donors table
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
		  secret VARCHAR(255),
		  mode VARCHAR(45) NOT NULL,
		  PRIMARY KEY (id)
		) $charset_collate";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

	}

	/**
	 * Creates the transactions table
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
		  secret VARCHAR(255),
		  PRIMARY KEY (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

	}

	/**
	 * Creates the subscription table
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
		  secret VARCHAR(255),		  
		  PRIMARY KEY (id)
		) $charset_collate";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

	}
}
