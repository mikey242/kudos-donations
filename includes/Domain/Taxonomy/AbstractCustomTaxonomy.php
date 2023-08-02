<?php
/**
 * AbstractCustomTaxonomy
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\Taxonomy;

use IseardMedia\Kudos\Domain\AbstractContentType;

/**
 * AbstractCustomPostType class.
 */
abstract class AbstractCustomTaxonomy extends AbstractContentType implements CustomTaxonomyInterface {

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->register_taxonomy();
	}

	/**
	 * Register the taxonomy with WordPress.
	 */
	private function register_taxonomy(): void {
		register_taxonomy(
			$this->get_slug(),
			$this->get_post_types(),
			$this->get_args()
		);
	}
}
