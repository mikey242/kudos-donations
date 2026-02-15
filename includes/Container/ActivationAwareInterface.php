<?php
/**
 * Defines method for running on plugin activation.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Container;

interface ActivationAwareInterface {

	/**
	 * Act on plugin activation.
	 */
	public function on_plugin_activation(): void;
}
