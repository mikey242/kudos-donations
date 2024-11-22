<?php
/**
 * AbstractCustomPostType
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\PostType;

use IseardMedia\Kudos\Admin\TableColumnsTrait;
use IseardMedia\Kudos\Domain\AbstractContentType;
use IseardMedia\Kudos\Domain\HasAdminColumns;
use IseardMedia\Kudos\Domain\HasMetaFieldsInterface;
use IseardMedia\Kudos\Domain\HasRestFieldsInterface;
use IseardMedia\Kudos\Domain\MapperTrait;
use IseardMedia\Kudos\Enum\ObjectType;

/**
 * AbstractCustomPostType class.
 */
abstract class AbstractCustomPostType extends AbstractContentType implements CustomPostTypeInterface {

	use MapperTrait;
	use TableColumnsTrait;

	/**
	 * Allows changing the capabilities of the CPT. By default, we want to disable post creation by the user.
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_post_type_capabilities/
	 */
	protected function get_capabilities(): array {
		return [ 'create_posts' => false ];
	}

	/**
	 * Returns supported features.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_post_type/#supports
	 */
	protected function get_supports(): array {
		return [ 'custom-fields', 'title' ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_args(): array {
		return array_merge( [ 'supports' => $this->get_supports() ], parent::get_args() );
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
	public function register(): void {
		$this->register_post_type();
		if ( is_a( $this, HasMetaFieldsInterface::class ) ) {
			$this->register_meta_fields( $this->get_meta_config(), ObjectType::POST, $this->get_slug() );
		}

		if ( is_a( $this, HasRestFieldsInterface::class ) ) {
			$this->register_rest_fields( $this->get_rest_fields(), $this->get_slug() );
		}

		if ( is_a( $this, HasAdminColumns::class ) ) {
			$this->add_table_columns( $this->get_slug(), $this->get_columns_config() );
		}
	}
}
