<?php
/**
 * Interface for defining Custom Content Types.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain;

interface ContentTypeInterface {

	/**
	 * Function that returns the name of the custom post type.
	 */
	public static function get_slug(): string;

	/**
	 * Function that returns the name of the custom post type.
	 */
	public function get_description(): string;

	/**
	 * Function that returns the arguments of the custom post type.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_post_type/#parameters
	 */
	public function get_args(): array;
}
