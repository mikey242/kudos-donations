<?php
/**
 * Parent Admin Page.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

class ParentAdminPage extends AbstractAdminPage {

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->register_parent_page();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_page_title(): string {
		return __( 'Kudos', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_menu_title(): string {
		return __( 'Donations', 'kudos-donations' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_menu_slug(): string {
		return 'kudos-campaigns';
	}

	/**
	 * Register the parent page.
	 */
	public function register_parent_page(): void {
		add_menu_page(
			__( 'Kudos', 'kudos-donations' ),
			__( 'Donations', 'kudos-donations' ),
			$this->get_capability(),
			$this->get_parent_slug(),
			false,
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			'data:image/svg+xml;base64,' . base64_encode(
				'<svg viewBox="0 0 555 449" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2"><path fill="#f0f5fa99" d="M0 65.107a65.114 65.114 0 0 1 19.07-46.04A65.114 65.114 0 0 1 65.11-.003h.002c36.09 0 65.346 29.256 65.346 65.346v317.713a65.292 65.292 0 0 1-19.125 46.171 65.292 65.292 0 0 1-46.171 19.125h-.001c-35.987 0-65.16-29.173-65.16-65.16L0 65.107ZM489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781Z"/></svg>'
			),
			$this->get_position()
		);
	}

	/**
	 * Determine position of Kudos in main menu.
	 *
	 * @see https://wordpress.stackexchange.com/questions/8779/placing-a-custom-post-type-menu-above-the-posts-menu-using-menu-position
	 */
	public function get_position(): ?int {
		return 75;
	}

	/**
	 * Needs to have a higher priority than other pages.
	 */
	public static function get_registration_action_priority(): int {
		return 1;
	}
}
