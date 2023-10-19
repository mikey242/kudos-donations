<?php
/**
 * Admin Page Interface.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

interface AdminPageInterface {

	/**
	 * Get the page title.
	 */
	public function get_page_title(): string;

	/**
	 * Get the menu title.
	 */
	public function get_menu_title(): string;

	/**
	 * Get the capability for access.
	 */
	public function get_capability(): string;

	/**
	 * Get the menu slug for this page.
	 */
	public function get_menu_slug(): string;

	/**
	 * Returns the slug for the parent page.
	 */
	public function get_parent_slug(): string;
}
