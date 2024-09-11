<?php
/**
 * Transactions Admin Page.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

use IseardMedia\Kudos\Domain\PostType\TransactionPostType;

class TransactionsAdminPage extends AbstractAdminPage {

	/**
	 * {@inheritDoc}
	 */
	public function get_page_title(): string {
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_menu_title(): string {
		return __( 'Transactions', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_menu_slug(): string {
		return 'edit.php?post_type=' . TransactionPostType::get_slug();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_position(): ?int {
		return 1;
	}
}
