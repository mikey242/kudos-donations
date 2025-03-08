<?php
/**
 * Mapper Trait.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Domain;

use WP_Post;
use WP_REST_Request;

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
	 * Gets the post using the rest API.
	 *
	 * @param int $post_id The ID of the post.
	 * @return mixed|null
	 */
	public static function get_post_using_rest( int $post_id ) {
		// Use internal REST request to get the donor.
		$request  = new WP_REST_Request( 'GET', '/wp/v2/' . static::get_slug() . "/{$post_id}" );
		$response = rest_do_request( $request );
		if ( $response->is_error() ) {
			return null; // Return null if donor is not found.
		}

		return $response->get_data(); // Return the donor's full REST response.
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
			return $post && static::get_slug() === $post->post_type ? $post : null;
		}

		// Ensure ID is not set.
		unset( $args['ID'] );

		// Return null if no args set.
		if ( empty( $args ) ) {
			return null;
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

		$new = true;

		// Save or update post.
		if ( $post_id ) {
			$post_id = wp_update_post( $post_data['post_data'], true );
		} else {
			$new     = false;
			$post_id = wp_insert_post( $post_data['post_data'], true );
		}

		// Bail if post not saved/updated.
		if ( is_wp_error( $post_id ) ) {
			return null;
		}

		// Update meta.
		self::save_meta_data( $post_id, $post_data );

		// Hook for acting on fully created post.
		do_action( static::get_slug() . '_post_saved', $post_id, $new );
		do_action( 'kudos_post_saved', $post_id, $new );

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
		$meta_data = [];
		$post_data = $args;

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
			'post_data' => wp_parse_args( $post_data, self::get_default_args() ),
			'meta_data' => $meta_data,
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
		if ( \array_key_exists( 'meta_data', $args ) && \is_array( $args['meta_data'] ) ) {
			foreach ( $args['meta_data'] as $meta_key => $meta_value ) {
				// Sanitize and save meta value.
				update_post_meta( $post_id, sanitize_key( $meta_key ), $meta_value );
			}
		}
	}

	/**
	 * Get the post by slug or ID.
	 *
	 * @param mixed $value The slug or ID to get.
	 */
	public static function get_post_by_id_or_slug( $value ): ?WP_Post {
		// Try to get the post by ID first.
		if ( is_numeric( $value ) ) {
			$post = get_post( (int) $value );
			if ( $post ) {
				return $post;
			}
		}

		// Get the post by slug if not found by ID.
		return get_page_by_path( sanitize_title( $value ), OBJECT, static::get_post_type() ) ?? null;
	}
}
