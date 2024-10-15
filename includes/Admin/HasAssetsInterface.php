<?php
/**
 * HasAssetsInterface.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

use WP_Screen;

interface HasAssetsInterface {

	/**
	 * Callback used for registering page assets.
	 */
	public function register_assets(): void;

	/**
	 * Determines if assets should be loaded or not.
	 *
	 * @param WP_Screen $screen Screen object.
	 */
	public function should_enqueue_assets( WP_Screen $screen ): bool;
}
