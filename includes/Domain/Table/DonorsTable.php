<?php
/**
 * Donors Table.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Table;

class DonorsTable extends BaseTable {

	/**
	 * {@inheritDoc}
	 */
	public static function get_name(): string {
		return 'kudos_donors';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_schema(): string {
		return '
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			wp_post_id bigint(20) unsigned DEFAULT NULL,
			title varchar(255) DEFAULT NULL,
			email varchar(255),
			mode varchar(20),
			name varchar(255),
			business_name varchar(255),
			street varchar(255),
			postcode varchar(50),
			city varchar(100),
			country char(2),
			vendor_customer_id varchar(255),
			locale char(5) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT NULL,
			KEY idx_post (wp_post_id),
			KEY idx_email (email),
			KEY idx_country (country),
            KEY idx_locale (locale),
            PRIMARY KEY  (id)
	';
	}
}
