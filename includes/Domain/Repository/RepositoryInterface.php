<?php
/**
 * Repository interface.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Domain\Repository;

interface RepositoryInterface {
	/**
	 * The singular name.
	 */
	public static function get_singular_name(): string;

	/**
	 * The plural name.
	 */
	public static function get_plural_name(): string;
}
