<?php
/**
 * Wrapper for the LoggerAwareTrait to ensure logger is always set.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Container;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

trait SafeLoggerTrait {

	use LoggerAwareTrait;

	/**
	 * Adds get_logger to ensure logger is set, throws error if not.
	 *
	 * @throws \LogicException If logger property not set.
	 */
	protected function get_logger(): LoggerInterface {
		if ( ! $this->logger ) {
			throw new \LogicException( 'Logger not set' );
		}

		return $this->logger;
	}
}
