<?php
/**
 * AbstractCustomPostType
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain\PostType;

use IseardMedia\Kudos\Admin\TableColumnsTrait;
use IseardMedia\Kudos\Domain\AbstractContentType;
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
		return array_merge(
			[
				'supports'        => $this->get_supports(),
				'capability_type' => 'post',
			],
			parent::get_args()
		);
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
	 * Add meta to REST query.
	 */
	private function add_meta_to_rest_query() {
		add_filter(
			'rest_' . $this->get_slug() . '_query',
			function ( $args, $request ) {
				$args += [
					'meta_key'   => $request['meta_key'],
					'meta_value' => $request['meta_value'],
					'meta_query' => $request['meta_query'],
				];

				return $args;
			},
			10,
			2
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		if ( ! post_type_exists( $this->get_slug() ) ) {
			$this->register_post_type();
		}
		if ( $this instanceof HasMetaFieldsInterface ) {
			$this->add_meta_to_rest_query();
			$this->register_meta_fields( apply_filters( $this->get_slug() . '_meta_fields', $this->get_meta_config() ), ObjectType::POST, $this->get_slug() );
		}

		if ( $this instanceof HasRestFieldsInterface ) {
			$this->register_rest_fields( apply_filters( $this->get_slug() . '_rest_fields', $this->get_rest_fields() ), $this->get_slug() );
		}
	}
}
