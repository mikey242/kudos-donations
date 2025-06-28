<?php
/**
 * BaseController.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Repository\RepositoryAwareInterface;
use IseardMedia\Kudos\Repository\RepositoryAwareTrait;

abstract class BaseController extends AbstractRegistrable implements RepositoryAwareInterface {

	use RepositoryAwareTrait;
}
