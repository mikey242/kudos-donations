<?php

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
 * @package           Cudo
 *
 * @wordpress-plugin
 * Plugin Name:       Cudo
 * Plugin URI:        https://iseard.media
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Michael Iseard
 * Author URI:        https://www.linkedin.com/in/michael-iseard/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cudo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CUDO_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cudo-activator.php
 */
function activate_cudo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cudo-activator.php';
	Cudo_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cudo-deactivator.php
 */
function deactivate_cudo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cudo-deactivator.php';
	Cudo_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cudo' );
register_deactivation_hook( __FILE__, 'deactivate_cudo' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cudo.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cudo() {

	$plugin = new Cudo();
	$plugin->run();

}
run_cudo();
