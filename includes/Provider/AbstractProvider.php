<?php
/**
 * Abstract vendor class.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

namespace IseardMedia\Kudos\Provider;

use IseardMedia\Kudos\Container\EncryptionAwareInterface;
use IseardMedia\Kudos\Container\EncryptionAwareTrait;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Container\SafeLoggerTrait;
use Psr\Log\LoggerAwareInterface;

abstract class AbstractProvider implements ProviderInterface, HasSettingsInterface, EncryptionAwareInterface, LoggerAwareInterface {
	use SafeLoggerTrait;
	use EncryptionAwareTrait;
}
