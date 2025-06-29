<?php
/**
 * Plugin initialization file.
 */

declare(strict_types=1);

namespace IseardMedia\Kudos;

use IseardMedia\Kudos\Service\CacheService;
use IseardMedia\Kudos\Service\CompatibilityService;
use IseardMedia\Kudos\Service\NoticeService;

/**
 * Handle plugin activation.
 */
function activate(): void {

	( new CompatibilityService() )->on_plugin_activation();

	PluginFactory::create()->on_plugin_activation();

	/**
	 * Fires when the plugin is activated.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 */
	do_action( 'kudos_donations_activated' );
}

register_activation_hook( KUDOS_PLUGIN_FILE, __NAMESPACE__ . '\activate' );

/**
 * Handles plugin deactivation.
 */
function deactivate(): void {
	PluginFactory::create()->on_plugin_deactivation();

	/**
	 * Fires after plugin deactivation.
	 *
	 * @param bool $network_wide Whether to deactivate network-wide.
	 */
	do_action( 'kudos_donations_deactivated' );
}

register_deactivation_hook( KUDOS_PLUGIN_FILE, __NAMESPACE__ . '\deactivate' );

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
	try {
		PluginFactory::create()->register();
	} catch ( \Throwable $e ) {
		NoticeService::notice( $e->getMessage(), NoticeService::ERROR );
		CacheService::recursively_clear_cache();
	}
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\bootstrap_plugin' );
