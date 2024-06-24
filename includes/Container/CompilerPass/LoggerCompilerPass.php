<?php
/**
 * Compiler pass that looks for services implementing LoggerAwareInterface
 * and automatically calls setLogger with currently logger as parameter.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\CompilerPass;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * ServiceCompilerPass class
 */
class LoggerCompilerPass implements CompilerPassInterface {

	/**
	 * Add an instance of the LoggerInterface to the relevant services.
	 *
	 * @param ContainerBuilder $container Our container.
	 */
	public function process( ContainerBuilder $container ): void {
		// Check if the Logger service is defined.
		if ( ! $container->has( LoggerInterface::class ) ) {
			return;
		}

		$logger_reference = new Reference( LoggerInterface::class );

		// Iterate over all service definitions.
		foreach ( $container->getDefinitions() as $definition ) {

			// Check if the service implements LoggerAwareInterface.
			if ( is_subclass_of( $definition->getClass(), LoggerAwareInterface::class ) ) {
				// Call the setLogger method with the Logger service.
				$definition->addMethodCall( 'setLogger', [ $logger_reference ] );
			}
		}
	}
}
