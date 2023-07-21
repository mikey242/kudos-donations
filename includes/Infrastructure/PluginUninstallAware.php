<?php
/**
 * Main Plugin class.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Infrastructure;

/**
 * Something that can be uninstalled.
 *
 * By tagging a service with this interface, the system will automatically hook
 * it up to the WordPress uninstall hook.
 *
 * This way, we can just add the simple interface marker and not worry about how
 * to wire up the code to reach that part during the static uninstall hook.
 *
 * @internal
 */
interface PluginUninstallAware {

	/**
	 * Act on plugin uninstall.
	 */
	public function on_plugin_uninstall(): void;
}