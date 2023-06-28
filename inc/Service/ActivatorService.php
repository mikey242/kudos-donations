<?php

namespace IseardMedia\Kudos\Service;

use IseardMedia\Kudos\Entity\DonorEntity;
use IseardMedia\Kudos\Entity\SubscriptionEntity;
use IseardMedia\Kudos\Entity\TransactionEntity;
use IseardMedia\Kudos\Helpers\Settings;
use IseardMedia\Kudos\Helpers\WpDb;
use Psr\Log\LoggerInterface;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class ActivatorService
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var TwigService
     */
    private TwigService $twig;
    /**
     * @var WpDb|\wpdb
     */
    private \wpdb|WpDb $wpdb;

    public function __construct(LoggerInterface $logger)
    {
        $this->wpdb = new WpDb();
        $this->logger = $logger;
        $this->twig = new TwigService($this->logger);
    }

    /**
     * Runs all activation functions.
     */
    public function activate(): void {
        self::create_log_table();
        self::create_donors_table();
        self::create_transactions_table();
        self::create_subscriptions_table();

        $logger = $this->logger;
        $twig   = $this->twig;
        $twig->init();

        $db_version = get_option('_kudos_donations_version');

        if ($db_version) {
            self::queue_migrations($db_version);
        }

        update_option('_kudos_donations_version', KUDOS_VERSION);
        $logger->info('Kudos Donations plugin activated.', ['version' => KUDOS_VERSION]);
    }

    /**
     * Creates the log table.
     */
    private function create_log_table(): void {
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
        dbDelta($sql);
    }

    /**
     * Creates the donors table.
     */
    private function create_donors_table(): void {
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
        dbDelta($sql);
    }

    /**
     * Creates the transactions table.
     */
    private function create_transactions_table(): void {
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
        dbDelta($sql);
    }

    /**
     * Creates the subscription table.
     */
    private function create_subscriptions_table(): void {
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
        dbDelta($sql);
    }

    /**
     * Run migrations if upgrading.
     *
     * @param string $db_version
     */
    private function queue_migrations(string $db_version): void {
        if (version_compare($db_version, KUDOS_VERSION, '<')) {
            $logger = $this->logger;

            $logger->info(
                'Upgrade detected, running migrations.',
                ['old_version' => $db_version, 'new_version' => KUDOS_VERSION]
            );

            if (version_compare($db_version, '4.0.0', '<')) {
                Settings::update_array('migrations_pending', ['400']);
            }
        }
    }
}
