<?php
/**
 * ContainerFactory class.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos;

use Exception;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Class ContainerFactory
 */
class ContainerFactory {

	/**
	 * Build and return the DI container.
	 *
	 * @throws RuntimeException | Exception If the container could not be built.
	 *
	 * @return ContainerInterface The compiled container.
	 */
	public static function create(): ContainerInterface {
		static $container = null;

		if ( null !== $container ) {
			return $container;
		}

		$kernel    = new Kernel( ! KUDOS_ENV_IS_DEVELOPMENT );
		$container = $kernel->get_container();

		if ( null === $container ) {
			throw new RuntimeException( 'Error fetching container' );
		}

		return $container;
	}
}
