<?php
/**
 * Add a donation button to any page on your website. Easy & fast setup. Works with Mollie payments.
 *
 * @link              https://www.linkedin.com/in/michael-iseard/
 * @author            Michael Iseard
 * @package           Kudos-Donations
 *
 * @wordpress-plugin
 * Plugin Name:       Kudos Donations
 * Plugin URI:        https://gitlab.iseard.media/michael/kudos-donations
 * Description:       Add a donation button to any page on your website. Easy & fast setup. Works with Mollie payments.
 * Version:           2.4.0
 * Author:            Iseard Media
 * Author URI:        https://iseard.media
 * Requires at least: 5.5
 * Requires PHP:      7.1
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kudos-donations
 * Domain Path:       /languages
 */

namespace Kudos;

use Kudos\Service\ActivatorService;
use Kudos\Service\CompatibilityService;
use Kudos\Service\DeactivatorService;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

// Load .env file if present.
if ( class_exists( \Dotenv\Dotenv::class ) && file_exists( __DIR__ . '/.env' ) ) {
	$dotenv = \Dotenv\Dotenv::createImmutable( __DIR__ );
	$dotenv->load();
}

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'KUDOS_VERSION', '2.4.0' );
define( 'KUDOS_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'KUDOS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'KUDOS_STORAGE_DIR', wp_upload_dir()['basedir'] . '/kudos-donations/' );
define( 'KUDOS_STORAGE_URL', wp_upload_dir()['baseurl'] . '/kudos-donations/' );
define( 'KUDOS_DEBUG', get_option( '_kudos_debug_mode' ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in app/Service/ActivatorService.php
 */
function activate_kudos() {
	require_once KUDOS_PLUGIN_DIR . '/app/Service/ActivatorService.php';
	ActivatorService::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in app/Service/DeactivatorService.php
 */
function deactivate_kudos() {
	require_once KUDOS_PLUGIN_DIR . '/app/Service/DeactivatorService.php';
	DeactivatorService::deactivate();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\activate_kudos' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\deactivate_kudos' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require KUDOS_PLUGIN_DIR . '/app/KudosDonations.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_kudos() {

	// Check compatibility and run kudos if OK
	$compatibility = new CompatibilityService();
	$continue = $compatibility->init();

	if ( $continue ) {
		$plugin = new KudosDonations();
		$plugin->run();
	}

}

run_kudos();
