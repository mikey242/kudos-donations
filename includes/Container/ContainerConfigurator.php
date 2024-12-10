<?php
/**
 * Configures the container.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Container;

use Psr\Container\ContainerInterface;

class ContainerConfigurator {
	/**
	 * Allows apply_filter global to be used in the container configuration.
	 *
	 * @param string             $filter_name The filter name passed.
	 * @param ContainerInterface $container Instance of container.
	 */
	public function apply_filters( string $filter_name, ContainerInterface $container ): void {
		apply_filters( $filter_name, $container );
	}
}
