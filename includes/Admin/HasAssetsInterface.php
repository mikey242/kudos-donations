<?php
/**
 * HasAssetsInterface.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

interface HasAssetsInterface {

	/**
	 * Callback used for registering page assets.
	 */
	public function register_assets(): void;
}
