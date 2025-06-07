<?php
/**
 * Subscriptions Admin Page.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

class SubscriptionsAdminPage extends AbstractReactSubPage {

	/**
	 * {@inheritDoc}
	 */
	public function get_page_title(): string {
		return __( 'Kudos subscriptions', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_menu_title(): string {
		return __( 'Subscriptions', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_menu_slug(): string {
		return 'kudos-subscriptions';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_position(): int {
		return 4;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_parent_slug(): string {
		return DonationsAdminPage::get_menu_slug();
	}
}
