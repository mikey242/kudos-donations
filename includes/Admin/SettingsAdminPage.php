<?php
/**
 * Settings Admin Page.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

class SettingsAdminPage extends AbstractReactSubPage {
	/**
	 * {@inheritDoc}
	 */
	public function get_page_title(): string {
		return __( 'Kudos settings', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_menu_title(): string {
		return __( 'Settings', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_menu_slug(): string {
		return 'kudos-settings';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_position(): int {
		return 6;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_parent_slug(): string {
		return DonationsAdminPage::get_menu_slug();
	}
}
