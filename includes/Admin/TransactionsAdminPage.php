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
use WP_Screen;

class TransactionsAdminPage extends AbstractAdminPage implements SubmenuAdminPageInterface, HasAssetsInterface {

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
	public static function get_position(): int {
		return 1;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_parent_slug(): string {
		return DonationsAdminPage::get_menu_slug();
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_assets(): void {
		echo '
			<style>
				.success {
					color: #00a32a !important;
					border-color: #00a32a !important;
				}
				.kudos-transaction-pdf {
					display: inline-flex !important;
					justify-content: center;
                    align-items: center;
				}
			</style>
		';
	}

	/**
	 * Determines if assets should be loaded or not.
	 *
	 * @param WP_Screen $screen Screen object.
	 */
	public function should_enqueue_assets( WP_Screen $screen ): bool {
		return 'edit-kudos_transaction' === $screen->id;
	}
}
