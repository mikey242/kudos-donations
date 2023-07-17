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
use IseardMedia\Kudos\Service\SettingsService;

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
				$kudos_uploads = wp_upload_dir()['basedir'] . '/kudos-donations/container';
				$container_builder->enableCompilation( $kudos_uploads );
				$container_builder->writeProxiesToFile( true, $kudos_uploads . '/proxies' );
			}

			$config_path = KUDOS_PLUGIN_DIR . 'config/';
			$container_builder->addDefinitions( $config_path . 'config.php' );
			try {
				$container = $container_builder->build();
				$plugin = $container->get( Plugin::class );
			} catch ( DependencyException | NotFoundException | \Exception $e ) {
				error_log( $e->getMessage() );
			}
		}

		return $plugin;
	}
}
