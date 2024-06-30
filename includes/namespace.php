<?php
/**
 * Plugin initialization file.
 */

declare(strict_types=1);

namespace IseardMedia\Kudos;

/**
 * Handle plugin activation.
 *
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 *
 * @param bool $network_wide Whether the plugin is being activated network-wide.
 */
function activate( $network_wide = false ): void {
	$network_wide = (bool) $network_wide;
	PluginFactory::create()->on_plugin_activation( $network_wide );

	/**
	 * Fires when the plugin is activated.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 */
	do_action( 'kudos_donations_activated', $network_wide );
}

register_activation_hook( KUDOS_PLUGIN_FILE, __NAMESPACE__ . '\activate' );

/**
 * Handles plugin deactivation.
 *
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 *
 * @param bool $network_wide Whether to deactivate network-wide.
 */
function deactivate( $network_wide = false ): void {
	$network_wide = (bool) $network_wide;
	PluginFactory::create()->on_plugin_deactivation( $network_wide );

	/**
	 * Fires after plugin deactivation.
	 *
	 * @param bool $network_wide Whether to deactivate network-wide.
	 */
	do_action( 'kudos_donations_deactivated', $network_wide );
}

register_deactivation_hook( KUDOS_PLUGIN_FILE, __NAMESPACE__ . '\deactivate' );

/**
 * Load functions for use by plugin developers.
 */
function load_functions(): void {
	require_once KUDOS_PLUGIN_DIR . 'includes/functions.php';
}

add_action( 'init', __NAMESPACE__ . '\load_functions' );

/**
 * Returns the Kudos Donations plugin instance.
 *
 * Can be used by other plugins to integrate with the plugin
 * or to simply detect whether the plugin is active.
 */
function get_plugin_instance(): Plugin {
	return PluginFactory::create();
}

/**
 * Bootstrap the plugin.
 */
function bootstrap_plugin(): void {
	PluginFactory::create()->register();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\bootstrap_plugin' );