<?php
/**
 * Compiler pass that looks for services implementing HasSettingsInterface.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\CompilerPass;

use IseardMedia\Kudos\Container\Handler\SettingsHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * ServiceCompilerPass class
 */
class SettingsCompilerPass implements CompilerPassInterface {

	/**
	 * Add an instance of the LoggerInterface to the relevant services.
	 *
	 * @param ContainerBuilder $container Our container.
	 */
	public function process( ContainerBuilder $container ): void {
		$handler     = $container->findDefinition( SettingsHandler::class );
		$definitions = $container->findTaggedServiceIds( 'kudos.settings' );

		foreach ( $definitions as $id => $definition ) {
			// Call the add method with the SettingsHandler.
			$handler->addMethodCall( 'add', [ new Reference( $id ) ] );
		}
	}
}
