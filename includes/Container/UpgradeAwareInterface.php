<?php
/**
 * Defines method for running on plugin upgrade.
 *
 * @link https://code.iseard.media/michael/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Container;

interface UpgradeAwareInterface {

	/**
	 * Act on plugin upgrade.
	 */
	public function on_plugin_upgrade(): void;
}
