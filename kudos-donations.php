<?php
/**
 * Add a donation button to any page on your website. Easy & fast setup. Works with Mollie payments.
 *
 * @link              https://www.linkedin.com/in/michael-iseard/
 *
 * @wordpress-plugin
 * Plugin Name:       Kudos Donations
 * Plugin URI:        https://github.com/mikey242/kudos-donations
 * Description:       Add a donation button to any page on your website. Easy & fast setup. Works with Mollie payments.
 * Version:           4.2.0-beta11
 * Author:            Iseard Media
 * Author URI:        https://iseard.media
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kudos-donations
 * Domain Path:       /languages
 */

namespace IseardMedia\Kudos;

use IseardMedia\Kudos\Service\CacheService;
use IseardMedia\Kudos\Service\CompatibilityService;
use IseardMedia\Kudos\Service\NoticeService;
use IseardMedia\Kudos\ThirdParty\Monolog\Logger;
use Symfony\Component\Dotenv\Dotenv;

// If this file is called directly, abort.
if ( ! \defined( 'WPINC' ) ) {
	die;
}

/**
 * Define all the Kudos Donations constants for use throughout the plugin.
 */
\define( 'KUDOS_VERSION', '4.2.0-beta11' );
\define( 'KUDOS_DB_VERSION', '4.2.0' );
\define( 'KUDOS_PLUGIN_FILE', __FILE__ );
\define( 'KUDOS_PLUGIN_URL', plugin_dir_url( KUDOS_PLUGIN_FILE ) );
\define( 'KUDOS_PLUGIN_DIR', plugin_dir_path( KUDOS_PLUGIN_FILE ) );
\define( 'KUDOS_CACHE_DIR', WP_CONTENT_DIR . '/cache/kudos-donations/' );
\define( 'KUDOS_DEBUG', (bool) get_option( '_kudos_debug_mode' ) );

if ( \function_exists( 'wp_upload_dir' ) ) {
	$upload_dir = wp_upload_dir();
	\define( 'KUDOS_STORAGE_URL', $upload_dir['baseurl'] . '/kudos-donations/' );
	\define( 'KUDOS_STORAGE_DIR', $upload_dir['basedir'] . '/kudos-donations/' );
}

// Autoloader for plugin.
require KUDOS_PLUGIN_DIR . 'includes/Autoloader.php';
if ( ! Autoloader::init() ) {
	return;
}

// Load the environment variables.
$dotenv = new Dotenv();
try {
	$dotenv->load( __DIR__ . '/.env' );
} catch ( \Exception $e ) {
	$_ENV['APP_ENV'] = 'production';
}

// Set the environment as production if not specified.
if ( ! isset( $_ENV['APP_ENV'] ) || '' === $_ENV['APP_ENV'] ) {
	$_ENV['APP_ENV'] = 'production';
}

// Action Scheduler.
if ( file_exists( KUDOS_PLUGIN_DIR . 'vendor/woocommerce/action-scheduler/action-scheduler.php' ) ) {
	include KUDOS_PLUGIN_DIR . 'vendor/woocommerce/action-scheduler/action-scheduler.php';
}

// Add additional env variables based on WordPress environment.
$dotenv->populate(
	[
		'KUDOS_STORAGE_DIR' => KUDOS_STORAGE_DIR,
		'LOG_LEVEL'         => KUDOS_DEBUG ? Logger::DEBUG : Logger::INFO,
	],
	true
);

// Define constant for easily accessing environment.
\define( 'KUDOS_APP_ENV', sanitize_text_field( $_ENV['APP_ENV'] ) );
\define( 'KUDOS_ENV_IS_DEVELOPMENT', 'development' === $_ENV['APP_ENV'] );

// Load dev commands if in dev environment.
$dev_bootstrap = __DIR__ . '/includes-dev/bootstrap.php';
if ( file_exists( $dev_bootstrap ) ) {
	require_once $dev_bootstrap;
}

/**
 * Handle plugin activation.
 */
function activate(): void {
	( new CompatibilityService() )->on_plugin_activation();

	try {
		PluginFactory::create()->on_plugin_activation();
	} catch ( \Throwable $e ) {
        // phpcs:disable WordPress.PHP.DevelopmentFunctions
		error_log( $e->getMessage() );
		NoticeService::notice( $e->getMessage(), NoticeService::ERROR );
	}
	/**
	 * Fires when the plugin is activated.
	 */
	do_action( 'kudos_donations_activated' );
}

register_activation_hook( KUDOS_PLUGIN_FILE, __NAMESPACE__ . '\activate' );

/**
 * Handles plugin deactivation.
 */
function deactivate(): void {
	try {
		PluginFactory::create()->on_plugin_deactivation();
	} catch ( \Throwable $e ) {
        // phpcs:disable WordPress.PHP.DevelopmentFunctions
		error_log( $e->getMessage() );
		NoticeService::notice( $e->getMessage(), NoticeService::ERROR );
	}

	/**
	 * Fires after plugin deactivation.
	 */
	do_action( 'kudos_donations_deactivated' );
}

register_deactivation_hook( KUDOS_PLUGIN_FILE, __NAMESPACE__ . '\deactivate' );

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
