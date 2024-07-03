<?php
/**
 * Inserts migrations into the MigrationService.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\CompilerPass;

use IseardMedia\Kudos\Service\MigratorService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * ServiceCompilerPass class
 */
class MigrationCompilerPass implements CompilerPassInterface {

	/**
	 * Modify container.
	 *
	 * @param ContainerBuilder $container The container builder.
	 */
	public function process( ContainerBuilder $container ): void {
		$handler     = $container->findDefinition( MigratorService::class );
		$definitions = $container->findTaggedServiceIds( 'kudos.migration' );

		foreach ( $definitions as $id => $definition ) {
			// Call the add method with the ActivationHandler.
			$handler->addMethodCall( 'add_migration', [ new Reference( $id ) ] );
		}
	}
}
