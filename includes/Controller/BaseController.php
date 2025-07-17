<?php
/**
 * BaseController.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Domain\Repository\RepositoryAwareInterface;
use IseardMedia\Kudos\Domain\Repository\RepositoryAwareTrait;

abstract class BaseController extends AbstractRegistrable implements RepositoryAwareInterface {

	use RepositoryAwareTrait;
}
