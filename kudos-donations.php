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
 * Version:           4.0.0-beta-6
 * Author:            Iseard Media
 * Author URI:        https://iseard.media
 * Requires at least: 5.5
 * Requires PHP:      7.2
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kudos-donations
 * Domain Path:       /languages
 */

namespace IseardMedia\Kudos;

use Symfony\Component\Dotenv\Dotenv;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

// If this file is called directly, abort.
if ( ! \defined( 'WPINC' ) ) {
	die;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

/**
 * Load the environment variables.
 */
$dotenv = new Dotenv();
$dotenv->load( __DIR__ . '/.env' );

/**
 * Set the environment as production if not specified.
 */
if ( empty( $_ENV['APP_ENV'] ) ) {
	$_ENV['APP_ENV'] = 'production';
}

/**
 * Define all the Kudos Donations constants for use throughout the plugin.
 */
\define( 'KUDOS_VERSION', '4.0.0-beta-6' );
\define( 'KUDOS_DB_VERSION', '4.0.0-beta-6' );
\define( 'KUDOS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
\define( 'KUDOS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
\define( 'KUDOS_PLUGIN_FILE', __FILE__ );
\define( 'KUDOS_STORAGE_URL', wp_upload_dir()['baseurl'] . '/kudos-donations/' );
\define( 'KUDOS_STORAGE_DIR', wp_upload_dir()['basedir'] . '/kudos-donations/' );
\define( 'KUDOS_DEBUG', get_option( '_kudos_debug_mode' ) );

/**
 * Check if we are in development mode and if so replace the default
 * error handler with a more developer friendly one.
 *
 * @link https://github.com/filp/whoops
 */
if ( class_exists( Run::class ) && WP_DEBUG ) {
	$run     = new Run();
	$handler = new PrettyPageHandler();

	// Set the title of the error page.
	$handler->setPageTitle( 'Whoops! There was a problem.' );
	$run->pushHandler( $handler );

	// Register the handler with PHP.
	$run->register();
}

// Main plugin initialization happens there so that this file is still parsable in PHP < 7.0.
require KUDOS_PLUGIN_DIR . '/inc/namespace.php';
