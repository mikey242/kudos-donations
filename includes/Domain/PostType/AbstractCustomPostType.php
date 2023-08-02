<?php
/**
 * AbstractCustomPostType
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\PostType;

use IseardMedia\Kudos\Admin\TableColumnsTrait;
use IseardMedia\Kudos\Domain\AbstractContentType;
use IseardMedia\Kudos\Domain\HasAdminColumns;
use IseardMedia\Kudos\Domain\HasMetaFieldsInterface;
use IseardMedia\Kudos\Domain\LabelsTrait;
use IseardMedia\Kudos\Domain\MapperTrait;
use IseardMedia\Kudos\Enum\ObjectType;

/**
 * AbstractCustomPostType class.
 */
abstract class AbstractCustomPostType extends AbstractContentType {

	use LabelsTrait;
	use MapperTrait;
	use TableColumnsTrait;

	protected const SUPPORTS = [ 'custom-fields', 'title' ];

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->register_post_type();
		if ( is_a( $this, HasMetaFieldsInterface::class ) ) {
			$this->register_meta_fields( $this::get_meta_config(), ObjectType::POST, static::get_slug() );
		}

		if ( is_a( $this, HasAdminColumns::class ) ) {
			$this->add_table_columns( $this::get_slug(), $this->get_columns_config() );
		}
	}

	/**
	 * Register the post type with WordPress.
	 */
	private function register_post_type(): void {
		register_post_type(
			$this->get_slug(),
			$this->get_args()
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_args(): array {
		return array_merge( [ 'supports' => static::SUPPORTS ], parent::get_args() );
	}
}
