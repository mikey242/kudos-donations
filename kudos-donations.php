<?php
/**
 * Add a donation button to any page on your website. Easy & fast setup. Works with Mollie payments.
 *
 * @link              https://www.linkedin.com/in/michael-iseard/
 *
 * @wordpress-plugin
 * Plugin Name:       Kudos Donations
 * Plugin URI:        https://gitlab.iseard.media/michael/kudos-donations
 * Description:       Add a donation button to any page on your website. Easy & fast setup. Works with Mollie payments.
 * Version:           4.0.0-alpha-9
 * Author:            Iseard Media
 * Author URI:        https://iseard.media
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kudos-donations
 * Domain Path:       /languages
 */

namespace IseardMedia\Kudos;

use Symfony\Component\Dotenv\Dotenv;

// If this file is called directly, abort.
if ( ! \defined( 'WPINC' ) ) {
	die;
}

/**
 * Define all the Kudos Donations constants for use throughout the plugin.
 */
\define( 'KUDOS_VERSION', '4.0.0-alpha-9' );
\define( 'KUDOS_DB_VERSION', '4.0.0' );
\define( 'KUDOS_PLUGIN_FILE', __FILE__ );
\define( 'KUDOS_PLUGIN_URL', plugin_dir_url( KUDOS_PLUGIN_FILE ) );
\define( 'KUDOS_PLUGIN_DIR', plugin_dir_path( KUDOS_PLUGIN_FILE ) );
\define( 'KUDOS_STORAGE_URL', wp_upload_dir()['baseurl'] . '/kudos-donations/' );
\define( 'KUDOS_STORAGE_DIR', wp_upload_dir()['basedir'] . '/kudos-donations/' );
\define( 'KUDOS_DEBUG', get_option( '_kudos_debug_mode' ) );
\define( 'KUDOS_SALT', NONCE_SALT );

// Autoloader for plugin.
if ( file_exists( KUDOS_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	include KUDOS_PLUGIN_DIR . 'vendor/autoload.php';
}

// Action Scheduler.
if ( file_exists( KUDOS_PLUGIN_DIR . '/vendor/woocommerce/action-scheduler/action-scheduler.php' ) ) {
	include KUDOS_PLUGIN_DIR . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
}

// Load the environment variables.
$dotenv = new Dotenv();
try {
	$dotenv->load( __DIR__ . '/.env' );
	// phpcs:ignore
} catch ( \Exception $ignored ) {
}

// Set the environment as production if not specified.
if ( empty( $_ENV['APP_ENV'] ) ) {
	$_ENV['APP_ENV'] = 'production';
}

// Set Whoops as error handler if in development.
if ( 'production' !== $_ENV['APP_ENV'] && class_exists( '\Whoops\Run' ) ) {
	$whoops = new \Whoops\Run();
	$whoops->pushHandler( new \Whoops\Handler\PrettyPageHandler() );
	$whoops->register();
}

require KUDOS_PLUGIN_DIR . '/includes/namespace.php';
