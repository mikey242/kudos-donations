<?php
/**
 * Compiler pass that looks for instances of Registrable
 * and uses the service handler to queue and instantiate them.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Infrastructure\Container\CompilerPass;

use IseardMedia\Kudos\Infrastructure\Container\Handler\ServiceHandler;
use IseardMedia\Kudos\Infrastructure\Container\Registrable;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * ServiceCompilerPass class
 */
class ServiceCompilerPass implements CompilerPassInterface {

	/**
	 * Modify container.
	 *
	 * @param ContainerBuilder $container The container builder.
	 */
	public function process( ContainerBuilder $container ): void {
		$handler     = $container->getDefinition( ServiceHandler::class );
		$definitions = $container->getDefinitions();
		foreach ( $definitions as $id => $definition ) {
			// Check if the service implements Registrable interface.
			if ( is_a( $definition->getClass(), Registrable::class, true ) ) {
				// Call the add method with the ServiceHandler.
				$handler->addMethodCall( 'add', [ new Reference( $id ) ] );
			}
		}
	}
}
