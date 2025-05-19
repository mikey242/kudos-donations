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
			'rest_' . $this->get_slug() . '_collection_params',
			function ( $params ) {
				$params['orderby']['enum'][] = 'meta_value';
				$params['orderby']['enum'][] = 'meta_value_num';
				return $params;
			}
		);
		add_filter(
			'rest_' . $this->get_slug() . '_query',
			function ( $args, $request ) {
				$meta_key = $request->get_param( 'metaKey' );
				if ( $meta_key ) {
					$args['meta_key']   = sanitize_key( $meta_key );
					$args['meta_value'] = sanitize_text_field( $request->get_param( 'metaValue' ) );

					$meta_type = $request->get_param( 'metaType' );
					if ( $meta_type ) {
						$args['meta_type'] = sanitize_key( $meta_type );
					}

					$meta_compare      = strtoupper( $request->get_param( 'metaCompare' ) ?? '=' );
					$valid_comparisons = [
						'=',
						'!=',
						'>',
						'>=',
						'<',
						'<=',
						'LIKE',
						'NOT LIKE',
						'IN',
						'NOT IN',
						'EXISTS',
						'NOT EXISTS',
					];

					if ( \in_array( $meta_compare, $valid_comparisons, true ) ) {
						$args['meta_compare'] = $meta_compare;
					}
				}
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

		if ( $this instanceof HasAdminColumns ) {
			$this->add_table_columns( $this->get_slug(), $this->get_columns_config() );
		}
	}
}
