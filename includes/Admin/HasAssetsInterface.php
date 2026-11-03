<?php
/**
 * HasAssetsInterface.
 *
 * @link https://code.iseard.media/michael/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

interface HasAssetsInterface {

	/**
	 * Callback used for registering page assets.
	 */
	public function register_assets(): void;
}
