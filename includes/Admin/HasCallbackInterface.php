<?php
/**
 * Interface for classes with a callback.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Admin;

interface HasCallbackInterface {

	/**
	 * The function to be called to output the content for this page.
	 */
	public function callback(): void;
}
