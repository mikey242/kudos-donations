<?php
/**
 * Has Admin Columns Interface.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Infrastructure\Domain;

interface HasAdminColumns {

	/**
	 * Returns an array of columns config.
	 */
	public function get_columns_config(): array;

}
