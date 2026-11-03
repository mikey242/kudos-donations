<?php
/**
 * Submenu Admin Page Interface.
 *
 * @link https://code.iseard.media/michael/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

interface SubmenuAdminPageInterface extends AdminPageInterface {
	/**
	 * Returns the parent page slug.
	 */
	public function get_parent_slug(): string;
}
