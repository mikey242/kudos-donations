<?php
/**
 * Uninstall handler.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare( strict_types=1 );

use IseardMedia\Kudos\PluginFactory;

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

PluginFactory::create()->register();

/**
 * Defer running uninstall until every service is registered.
 */
function kudos_donations_uninstall(): void {
	$plugin = PluginFactory::create();
	$plugin->register();
	$plugin->on_plugin_uninstall();
}
add_action( 'shutdown', 'kudos_donations_uninstall' );
