<?php
/**
 * HasAssetsInterface.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

interface HasAssetsInterface {

	/**
	 * Callback used for registering page assets.
	 */
	public function register_assets(): void;
}
