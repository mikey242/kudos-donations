<?php

namespace Kudos;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.linkedin.com/in/michael-iseard/
 * @since             1.0.0
 * @package           Kudos-Donations
 *
 * @wordpress-plugin
 * Plugin Name:       Kudos Donations
 * Plugin URI:        https://gitlab.iseard.media/michael/kudos-donations
 * Description:       Add a donation button to any page on your website. Easy & fast setup. Works with Mollie payments.
 * Version:           2.0.0
 * Author:            Iseard Media
 * Author URI:        https://iseard.media
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kudos-donations
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'KUDOS_VERSION', '2.0.0' );
define('KUDOS_DIR', plugin_dir_path(__FILE__));
define('KUDOS_DEBUG', get_option('_kudos_debug_mode'));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-kudos-activator.php
 */
function activate_kudos() {
	require_once KUDOS_DIR . 'includes/class-kudos-activator.php';
	Kudos_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-kudos-deactivator.php
 */
function deactivate_kudos() {
	require_once KUDOS_DIR . 'includes/class-kudos-deactivator.php';
	Kudos_Deactivator::deactivate();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\activate_kudos' );
register_deactivation_hook( __FILE__,  __NAMESPACE__ . '\deactivate_kudos' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require KUDOS_DIR . 'includes/class-kudos.php';

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

	$plugin = new Kudos_Donations();
	$plugin->run();

}

run_kudos();
