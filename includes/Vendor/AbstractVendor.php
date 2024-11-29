<?php
/**
 * Abstract Newsletter provider.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2024 Iseard Media
 */

namespace IseardMedia\Kudos\Vendor;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\EncryptionAwareInterface;
use IseardMedia\Kudos\Container\EncryptionAwareTrait;
use IseardMedia\Kudos\Container\HasSettingsInterface;

abstract class AbstractVendor extends AbstractRegistrable implements VendorInterface, HasSettingsInterface, EncryptionAwareInterface {
    use EncryptionAwareTrait;
}
