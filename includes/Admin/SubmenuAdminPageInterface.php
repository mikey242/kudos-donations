<?php
/**
 * Submenu Admin Page Interface.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

interface SubmenuAdminPageInterface extends AdminPageInterface {
	/**
	 * Returns the parent page slug.
	 */
	public function get_parent_slug(): string;
}
