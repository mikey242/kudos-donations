<?php
/**
 * Transactions Table.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Table;

class TransactionsTable extends BaseTable {

	/**
	 * {@inheritDoc}
	 */
	public static function get_name(): string {
		return 'kudos_transactions';
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
			status varchar(20) NOT NULL,
			method varchar(50),
			mode varchar(20),
			sequence_type varchar(20),
			donor_id bigint(20) unsigned DEFAULT NULL,
			campaign_id bigint(20) unsigned DEFAULT NULL,
			subscription_id bigint(20) unsigned DEFAULT NULL,
			vendor varchar(100),
			vendor_customer_id varchar(255),
			vendor_payment_id varchar(255),
			invoice_number bigint(20) unsigned DEFAULT NULL,
			checkout_url text DEFAULT NULL,
			message text DEFAULT NULL,
			refunds text DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT NULL,
			KEY idx_status (status),
			KEY idx_campaign (campaign_id),
			KEY idx_donor (donor_id),
			KEY idx_subscription (subscription_id),
			KEY idx_vendor_payment (vendor_payment_id(191)),
			KEY idx_vendor_customer (vendor_customer_id(191)),
			KEY idx_post (wp_post_id),
			PRIMARY KEY  (id)
	";
	}
}
