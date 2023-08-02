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
use IseardMedia\Kudos\Domain\HasMetaFieldsInterface;
use IseardMedia\Kudos\Enum\ObjectType;

/**
 * AbstractCustomPostType class.
 */
abstract class AbstractCustomTaxonomy extends AbstractContentType implements CustomTaxonomyInterface {

	/**
	 * Allows changing the capabilities of the CPT. By default, we want to disable post creation by the user.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_taxonomy/#arguments
	 */
	protected const CAPABILITIES = [];

	/**
	 * Default term(s) to use for the taxonomy.
	 */
	protected const DEFAULT_TERM = [];

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->register_taxonomy();
		if ( is_a( $this, HasMetaFieldsInterface::class ) ) {
			$this->register_meta_fields( $this::get_meta_config(), ObjectType::TERM, static::get_slug() );
		}
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

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action_priority(): int {
		return 20;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_args(): array {
		return array_merge( [ 'default_term' => static::DEFAULT_TERM ], parent::get_args() );
	}
}
