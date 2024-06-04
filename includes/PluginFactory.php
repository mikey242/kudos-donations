<?php
/**
 * PluginFactory class.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos;

use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;

/**
 * Class PluginFactory
 */
class PluginFactory {
	/**
	 * Create and return an instance of the plugin.
	 *
	 * This always returns a shared instance. This way, outside code can always
	 * get access to the object instance of the plugin.
	 *
	 * @return Plugin Plugin instance
	 */
	public static function create(): Plugin {
		static $plugin = null;

		if ( null === $plugin ) {
			$container_builder = new ContainerBuilder();
			// Enable cache if not in development mode.
			if ( 'development' !== $_ENV['APP_ENV'] ) {
				$cache_dir = wp_upload_dir()['basedir'] . '/kudos-donations/container';
				$container_builder->enableCompilation( $cache_dir );
				$container_builder->writeProxiesToFile( true, $cache_dir . '/proxies' );
			}

			$config_path = KUDOS_PLUGIN_DIR . 'config/';
			$container_builder->addDefinitions( $config_path . 'config.php' );
			try {
				$container = $container_builder->build();
				$plugin    = $container->get( Plugin::class );
			} catch ( DependencyException | NotFoundException | \Exception $e ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( $e->getMessage() );
			}
		}

		return $plugin;
	}
}
