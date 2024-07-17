<?php
/**
 * Mapper Trait.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain;

use WP_Post;

trait MapperTrait {

	/**
	 * The default arguments.
	 */
	private static function get_default_args(): array {
		return [
			'post_content' => '',
			'post_type'    => static::get_post_type(),
			'post_status'  => 'publish',
			'post_author'  => '',
		];
	}

	/**
	 * Gets the current post type.
	 */
	private static function get_post_type(): string {
		return \in_array(
			ContentTypeInterface::class,
			class_implements( static::class ),
			true
		) ? static::get_slug() : 'post';
	}

	/**
	 * Returns all posts between specified dates.
	 *
	 * @param string $start Start date.
	 * @param string $end End date.
	 */
	public static function get_all_between( string $start, string $end ): array {

		$args = self::prepare_arguments(
			[
				'posts_per_page' => -1,
				'date_query'     => [
					[
						'after'     => $start, // 'January 1st, 2015'
						'before'    => $end, // 'December 31st, 2015'
						'inclusive' => true,
					],
				],
			]
		);

		return get_posts( $args['post_data'] );
	}

	/**
	 * Gets posts by simple [key => value] args. Returns all posts of parent type if args empty.
	 *
	 * @param array  $args Array of [key => value] args.
	 * @param string $meta_relation AND / OR relation between meta values.
	 * @return WP_Post[]|int[] Array of post objects or post IDs.
	 */
	public static function get_posts( array $args = [], string $meta_relation = 'AND' ): array {

		$prepared_args = self::prepare_arguments(
			array_merge(
				[
					'posts_per_page' => -1,
					'relation'       => $meta_relation,
				],
				$args
			)
		);

		$query = $prepared_args['post_data'];

		foreach ( $prepared_args['meta_data'] as $key => $value ) {
			$query['meta_query'][] = [
				'key'   => $key,
				'value' => $value,
			];
		}

		return get_posts( array_filter( $query ) );
	}

	/**
	 * Gets a single post by simple [key => value] args.
	 *
	 * @param array  $args Array of key => value meta args.
	 * @param string $relation AND / OR relation between values.
	 */
	public static function get_post( array $args, string $relation = 'AND' ): ?WP_Post {

		// Return post using WordPress get_post() if ID present.
		if ( isset( $args['ID'] ) && $args['ID'] > 0 ) {
			$post = get_post( $args['ID'] );
			return static::get_slug() === $post->post_type ? $post : null;
		}

		$posts = static::get_posts( $args, $relation );
		if ( $posts ) {
			return $posts[0];
		}

		return null;
	}

	/**
	 * Create or update a post.
	 *
	 * @see https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters.
	 *
	 * @param array $args Array of post fields and meta.
	 */
	public static function save( array $args = [] ): ?WP_Post {

		$post_id = isset( $args['ID'] ) ? absint( $args['ID'] ) : 0;

		// Prepare post data.
		$post_data = self::prepare_arguments( $args );

		// Save or update post.
		if ( $post_id ) {
			$post_id = wp_update_post( $post_data['post_data'], true );
		} else {
			$post_id = wp_insert_post( $post_data['post_data'], true );
		}

		// Bail if post not saved/updated.
		if ( is_wp_error( $post_id ) ) {
			return null;
		}

		// Update meta.
		self::save_meta_data( $post_id, $post_data );

		// Return post object or null.
		return get_post( $post_id );
	}

	/**
	 * Adds defaults and sorts arguments by post_data and meta_data (if applicable).
	 *
	 * @param array $args Array of arguments to sort.
	 * @return array{post_data: array, meta_data: array}
	 */
	private static function prepare_arguments( array $args ): array {
		if ( \in_array( HasMetaFieldsInterface::class, class_implements( static::class ), true ) ) {
			/**
			 * HasMetaFieldsInterface is implemented.
			 *
			 * @var HasMetaFieldsInterface $current_class
			 */
			$current_class = static::class;
			$meta_config   = $current_class::get_meta_config();
			$meta_data     = array_intersect_key( $args, $meta_config );
			$post_data     = array_diff_key( $args, $meta_config );
		}

		return [
			'post_data' => wp_parse_args( $post_data ?? $args, self::get_default_args() ),
			'meta_data' => $meta_data ?? [],
		];
	}

	/**
	 * Saves meta values for the specified post.
	 *
	 * @param int   $post_id The post id to use.
	 * @param array $args Array of meta fields and values.
	 */
	private static function save_meta_data( int $post_id, array $args ): void {
		// Check if custom meta fields are provided.
		if ( isset( $args['meta_data'] ) && \is_array( $args['meta_data'] ) ) {
			foreach ( $args['meta_data'] as $meta_key => $meta_value ) {
				// Sanitize and save meta value.
				update_post_meta( $post_id, sanitize_key( $meta_key ), $meta_value );
			}
		}
	}
}
