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

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class PluginFactory
 */
class PluginFactory {

	/**
	 * Create and return an instance of the plugin.
	 *
	 * @throws \RuntimeException | NotFoundExceptionInterface | ContainerExceptionInterface If container fails or does not contain the Plugin class.
	 *
	 * @return Plugin Plugin instance.
	 */
	public static function create(): Plugin {
		static $plugin = null;

		if ( null !== $plugin ) {
			/** @var Plugin $plugin */
			return $plugin;
		}

		$kernel    = new Kernel( false );
		$container = $kernel->get_container();

		if ( null === $container ) {
			throw new \RuntimeException( 'Error fetching container' );
		}

		$plugin = $container->get( Plugin::class );

		if ( ! $plugin instanceof Plugin ) {
			throw new \RuntimeException( 'Error loading Kudos Donations: Resolved plugin is not a Plugin instance.' );
		}

		return $plugin;
	}
}
