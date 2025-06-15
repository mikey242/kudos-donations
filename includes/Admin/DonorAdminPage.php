<?php
/**
 * Transactions Admin Page.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 * @see \IseardMedia\Kudos\Domain\PostType\DonorPostType
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

class DonorAdminPage extends AbstractReactSubPage {

	/**
	 * {@inheritDoc}
	 */
	public function get_page_title(): string {
		return __( 'Kudos donors', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_menu_title(): string {
		return __( 'Donors', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_menu_slug(): string {
		return 'kudos-donors';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_position(): int {
		return 5;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_parent_slug(): string {
		return DonationsAdminPage::get_menu_slug();
	}
}
