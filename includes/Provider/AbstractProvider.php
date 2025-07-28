<?php
/**
 * Abstract vendor class.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

namespace IseardMedia\Kudos\Provider;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\EncryptionAwareInterface;
use IseardMedia\Kudos\Container\EncryptionAwareTrait;
use IseardMedia\Kudos\Container\HasSettingsInterface;

abstract class AbstractProvider extends AbstractRegistrable implements ProviderInterface, HasSettingsInterface, EncryptionAwareInterface {
	use EncryptionAwareTrait;
}
