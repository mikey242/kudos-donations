<?php
/**
 * PluginFactory class.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos;

use IseardMedia\Kudos\Service\NoticeService;

/**
 * Class PluginFactory
 */
class PluginFactory {

	/**
	 * Create and return an instance of the plugin.
	 *
	 * @throws \RuntimeException If plugin instance cannot be created.
	 *
	 * @return Plugin Plugin instance
	 */
	public static function create(): Plugin {
		static $plugin = null;

		if ( null !== $plugin ) {
			return $plugin;
		}

		try {
			$kernel    = new Kernel( false );
			$container = $kernel->get_container();
			$plugin    = $container->get( Plugin::class );

			if ( ! $plugin instanceof Plugin ) {
				throw new \RuntimeException( 'Error loading Kudos Donations: Resolved plugin is not a Plugin instance.' );
			}
		} catch ( \Throwable  $e ) {
			// phpcs:disable WordPress.PHP.DevelopmentFunctions
			error_log( $e->getMessage() );
			NoticeService::notice( $e->getMessage(), NoticeService::ERROR );
		}

		return $plugin;
	}
}
