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
 * Version:           3.2.2
 * Author:            Iseard Media
 * Author URI:        https://iseard.media
 * Requires at least: 5.5
 * Requires PHP:      7.2
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kudos-donations
 * Domain Path:       /languages
 */

namespace Kudos;

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Kudos\Service\ActivatorService;
use Kudos\Service\CompatibilityService;
use Kudos\Service\DeactivatorService;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

/**
 * Load the .env file if present.
 *
 * @link https://github.com/vlucas/phpdotenv
 */
if ( class_exists( Dotenv::class ) ) {
	$dotenv = Dotenv::createImmutable( __DIR__ );
	$dotenv->safeLoad();
}

/**
 * Define all the Kudos Donations constants for use throughout the plugin.
 */
define( 'KUDOS_VERSION', '3.2.2' );
define( 'KUDOS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'KUDOS_PLUGIN_DIR', __DIR__ );
define( 'KUDOS_STORAGE_URL', wp_upload_dir()['baseurl'] . '/kudos-donations/' );
define( 'KUDOS_STORAGE_DIR', wp_upload_dir()['basedir'] . '/kudos-donations/' );
define( 'KUDOS_DEBUG', get_option( '_kudos_debug_mode' ) );
define( 'KUDOS_SALT', NONCE_SALT );

/**
 * Check if we are in development mode and if so replace the default
 * error handler with a more developer friendly one.
 *
 * @link https://github.com/filp/whoops
 */
if ( class_exists( Run::class ) && ( $_ENV['WP_ENV'] ?? '' ) === 'development' ) {

	$run     = new Run();
	$handler = new PrettyPageHandler();

	// Set the title of the error page.
	$handler->setPageTitle( 'Whoops! There was a problem.' );
	$run->pushHandler( $handler );

	// Register the handler with PHP.
	$run->register();
}

/**
 * The code that runs during plugin activation.
 */
function activate_kudos() {
	$activator = new ActivatorService();
	$activator->activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_kudos() {
	DeactivatorService::deactivate();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\activate_kudos' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\deactivate_kudos' );

/**
 * The core plugin class that is used to define admin-specific hooks
 * and public-facing site hooks.
 */
require KUDOS_PLUGIN_DIR . '/app/KudosDonations.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function run_kudos_donations() {

	// Check compatibility and run kudos if OK
	$compatibility = new CompatibilityService();

	if ( $compatibility->init() ) {

		// Create our container for dependency injection.
		$builder = new ContainerBuilder();
		$builder->useAutowiring( true );

		// Get definitions for building container.
		$definitions = apply_filters(
			'kudos_container_definitions',
			array(
				KUDOS_PLUGIN_DIR . '/app/config.php',
			)
		);

		// Add definitions to container builder.
		foreach ( $definitions as $definition ) {
			$builder->addDefinitions( $definition );
		}

		// Allow container to be dumped to a file if in production.
		if ( isset( $_ENV['WP_ENV'] ) && 'development' !== $_ENV['WP_ENV'] ) {
			$builder->enableCompilation( KUDOS_STORAGE_DIR . '/php-di/cache' );
		}

		// Build container.
		$container = $builder->build();

		// Create action to provide container to addons.
		do_action( 'kudos_container_ready', $container );

		// Create and run our main plugin class.
		$plugin = new KudosDonations( $container, KUDOS_VERSION, 'kudos-donations' );
		$plugin->run();
	}
}

run_kudos_donations();
