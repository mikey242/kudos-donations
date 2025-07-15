<?php
/**
 * Campaigns Table.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Table;

class CampaignsTable extends BaseTable {

	/**
	 * {@inheritDoc}
	 */
	public static function get_name(): string {
		return 'kudos_campaigns';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_schema(): string {
		return "
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			wp_post_id bigint(20) unsigned DEFAULT NULL,
			wp_post_slug varchar(128) DEFAULT NULL,
			title varchar(255) DEFAULT NULL,
			currency char(3) NOT NULL DEFAULT 'EUR',
			goal decimal(10, 2) DEFAULT NULL,
			show_goal tinyint(1) DEFAULT 0,
			additional_funds decimal(10, 2) DEFAULT NULL,
			amount_type varchar(20) DEFAULT 'fixed',
			fixed_amounts text DEFAULT NULL,
			minimum_donation decimal(10,2) DEFAULT NULL,
			maximum_donation decimal(10,2) DEFAULT NULL,
			donation_type varchar(20) DEFAULT 'oneoff',
			frequency_options text DEFAULT NULL,
			email_enabled tinyint(1) DEFAULT 1,
			email_required tinyint(1) DEFAULT 1,
			name_enabled tinyint(1) DEFAULT 1,
			name_required tinyint(1) DEFAULT 1,
			address_enabled tinyint(1) DEFAULT 0,
			address_required tinyint(1) DEFAULT 0,
			message_enabled tinyint(1) DEFAULT 0,
			message_required tinyint(1) DEFAULT 0,
			theme_color varchar(20) DEFAULT '#ff9f1c',
			terms_link text DEFAULT NULL,
			privacy_link text DEFAULT NULL,
			show_return_message tinyint(1) DEFAULT 0,
			use_custom_return_url tinyint(1) DEFAULT 0,
			custom_return_url text DEFAULT NULL,
			payment_description_format text DEFAULT NULL,
			custom_styles text DEFAULT NULL,
			initial_title text DEFAULT NULL,
			initial_description text DEFAULT NULL,
			subscription_title text DEFAULT NULL,
			subscription_description text DEFAULT NULL,
			address_title text DEFAULT NULL,
			address_description text DEFAULT NULL,
			message_title text DEFAULT NULL,
			message_description text DEFAULT NULL,
			payment_title text DEFAULT NULL,
			payment_description text DEFAULT NULL,
			return_message_title text DEFAULT NULL,
			return_message_text text DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT NULL,
			KEY idx_post (wp_post_id),
			PRIMARY KEY  (id)
	";
	}
}
