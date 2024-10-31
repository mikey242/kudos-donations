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

use IseardMedia\Kudos\Service\NoticeService;
use Psr\Container\ContainerExceptionInterface;

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
			try {
				$kernel    = new Kernel();
				$container = $kernel->get_container();
				$plugin    = $container->get( Plugin::class );
			} catch ( ContainerExceptionInterface  $e ) {
				// phpcs:disable WordPress.PHP.DevelopmentFunctions
				error_log( $e->getMessage() );
				NoticeService::notice( NoticeService::ERROR, $e->getMessage() );
			}
		}

		return $plugin;
	}
}
