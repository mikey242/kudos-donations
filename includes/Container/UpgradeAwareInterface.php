<?php
/**
 * Defines method for running on plugin upgrade.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Container;

interface UpgradeAwareInterface {

	/**
	 * Act on plugin uninstall.
	 */
	public function on_plugin_upgrade(): void;
}
