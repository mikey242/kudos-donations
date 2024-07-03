<?php
/**
 * Creates a dismissible admin notice.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin\Notice;

/**
 * AdminDismissibleNotice Class
 */
class AdminDismissibleNotice extends AdminNotice {
	/**
	 * {@inheritDoc}
	 */
	public static function is_dismissible(): bool {
		return true;
	}
}
