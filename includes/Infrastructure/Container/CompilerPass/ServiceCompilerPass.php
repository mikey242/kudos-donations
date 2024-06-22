<?php
/**
 * Compiler pass that looks for instances of Registrable
 * and uses the service instantiator to queue and instantiate them.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Infrastructure\Container\CompilerPass;

use IseardMedia\Kudos\Infrastructure\Container\Registrable;
use IseardMedia\Kudos\Infrastructure\Container\ServiceInstantiator;
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
		$manager_definition = $container->getDefinition( ServiceInstantiator::class );
		$definitions        = $container->getDefinitions();
		foreach ( $definitions as $definition_id => $definition ) {
			$definition_class = $definition->getClass();
			if ( null === $definition_class || $definition->hasTag( 'container.excluded' ) ) {
				continue;
			}
			if ( ! is_a( $definition_class, Registrable::class, true ) ) {
				continue;
			}

			$manager_definition->addMethodCall(
				'add',
				[ new Reference( $definition_id ) ]
			);
		}
	}
}
