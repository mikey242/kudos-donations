<?php
/**
 * Parent Admin Page Interface.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

interface ParentAdminPageInterface extends AdminPageInterface {
	/**
	 * Returns the icon to be used in the menu.
	 */
	public function get_icon_url(): string;
}
