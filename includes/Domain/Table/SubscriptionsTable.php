<?php
/**
 * Subscriptions Table.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Table;

class SubscriptionsTable extends BaseTable {

	/**
	 * {@inheritDoc}
	 */
	public static function get_name(): string {
		return 'kudos_subscriptions';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_schema(): string {
		return "
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
	";
	}
}
