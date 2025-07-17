<?php
/**
 * Uninstall handler.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

use IseardMedia\Kudos\PluginFactory;
use IseardMedia\Kudos\Service\NoticeService;

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

try {
	PluginFactory::create()->register();
} catch ( Throwable $e ) {
	// phpcs:disable WordPress.PHP.DevelopmentFunctions
	error_log( $e->getMessage() );
	NoticeService::notice( $e->getMessage(), NoticeService::ERROR );
}

/**
 * Defer running uninstallation until every service is registered.
 */
function kudos_donations_uninstall(): void {
	try {
		$plugin = PluginFactory::create();
		$plugin->register();
		$plugin->on_plugin_uninstall();
	} catch ( Throwable $e ) {
		// phpcs:disable WordPress.PHP.DevelopmentFunctions
		error_log( $e->getMessage() );
		NoticeService::notice( $e->getMessage(), NoticeService::ERROR );
	}
}
add_action( 'shutdown', 'kudos_donations_uninstall' );
