<?php
/**
 * Constants for Kudos Donations.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

const KUDOS_VERSION    = '4.2.0';
const KUDOS_DB_VERSION = '4.2.0';
define( 'KUDOS_PLUGIN_FILE', dirname( __DIR__, 1 ) . '/kudos-donations.php' );
define( 'KUDOS_PLUGIN_URL', plugin_dir_url( KUDOS_PLUGIN_FILE ) );
define( 'KUDOS_PLUGIN_DIR', plugin_dir_path( KUDOS_PLUGIN_FILE ) );
const KUDOS_CACHE_DIR = WP_CONTENT_DIR . '/cache/kudos-donations/';
define( 'KUDOS_DEBUG', (bool) get_option( '_kudos_debug_mode' ) );
if ( defined( 'NONCE_SALT' ) ) {
	define( 'KUDOS_SALT', (string) NONCE_SALT );
}
if ( defined( 'AUTH_KEY' ) ) {
	define( 'KUDOS_AUTH_KEY', (string) AUTH_KEY );
}
if ( defined( 'AUTH_SALT' ) ) {
	define( 'KUDOS_AUTH_SALT', (string) AUTH_SALT );
}

if ( function_exists( 'wp_upload_dir' ) ) {
	$upload_dir = wp_upload_dir();
	define( 'KUDOS_STORAGE_URL', $upload_dir['baseurl'] . '/kudos-donations/' );
	define( 'KUDOS_STORAGE_DIR', $upload_dir['basedir'] . '/kudos-donations/' );
}
