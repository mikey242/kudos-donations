<?php
/**
 * Defines method for running on plugin activation.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Container;

interface ActivationAwareInterface {

	/**
	 * Act on plugin uninstall.
	 */
	public function on_plugin_activation(): void;
}
