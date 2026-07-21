<?php
/**
 * Uninstall handler.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare( strict_types=1 );

use IseardMedia\Kudos\ContainerFactory;
use IseardMedia\Kudos\Plugin;

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Defer running uninstallation until every service is registered.
 */
function kudos_donations_uninstall(): void {
	try {
		$plugin = ContainerFactory::create()->get( Plugin::class );
		$plugin->register();
		$plugin->on_plugin_uninstall();
	} catch ( Throwable $e ) {
		// phpcs:disable WordPress.PHP.DevelopmentFunctions
		error_log( $e->getMessage() );
	}
}
add_action( 'shutdown', 'kudos_donations_uninstall' );
