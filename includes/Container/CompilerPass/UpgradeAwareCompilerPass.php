<?php
/**
 * Compiler pass that looks for instances of Registrable
 * and uses the service handler to queue and instantiate them.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\CompilerPass;

use IseardMedia\Kudos\Container\Handler\UpgradeHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * ServiceCompilerPass class
 */
class UpgradeAwareCompilerPass implements CompilerPassInterface {

	/**
	 * Modify container.
	 *
	 * @param ContainerBuilder $container The container builder.
	 */
	public function process( ContainerBuilder $container ): void {
		$handler     = $container->findDefinition( UpgradeHandler::class );
		$definitions = $container->findTaggedServiceIds( 'kudos.upgradeable' );

		foreach ( $definitions as $id => $definition ) {
			// Call the add method with the ActivationHandler.
			$handler->addMethodCall( 'add', [ new Reference( $id ) ] );
		}
	}
}
