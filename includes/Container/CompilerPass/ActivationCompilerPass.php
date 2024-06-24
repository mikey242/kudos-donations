<?php
/**
 * Compiler pass that looks for services implementing ActivationAwareInterface
 * and automatically runs the on_activation when plugin activated.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\CompilerPass;

use IseardMedia\Kudos\Container\ActivationAwareInterface;
use IseardMedia\Kudos\Container\Handler\ActivationHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * ServiceCompilerPass class
 */
class ActivationCompilerPass implements CompilerPassInterface {

	/**
	 * Add an instance of the LoggerInterface to the relevant services.
	 *
	 * @param ContainerBuilder $container Our container.
	 */
	public function process( ContainerBuilder $container ): void {
		$handler = $container->findDefinition( ActivationHandler::class );
		foreach ( $container->getDefinitions() as $id => $definition ) {
			// Check if the service implements ActivationAwareInterface.
			if ( is_subclass_of( $definition->getClass(), ActivationAwareInterface::class ) ) {
				// Call the add method with the ActivationHandler.
				$handler->addMethodCall( 'add', [ new Reference( $id ) ] );
			}
		}
	}
}
