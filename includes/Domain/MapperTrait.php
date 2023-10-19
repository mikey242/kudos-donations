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

use WP_Error;
use WP_Post;

trait MapperTrait {

	/**
	 * Returns the slug of the current post type.
	 */
	abstract public static function get_slug(): string;

	/**
	 * The default arguments.
	 */
	public static function get_default_args(): array {
		return [
			'post_type'   => static::get_slug(),
			'post_status' => 'publish',
		];
	}

	/**
	 * Gets all posts for current post type.
	 */
	public static function get_all(): array {
		return get_posts( self::get_default_args() );
	}

	/**
	 * Gets posts by simple key => value args.
	 *
	 * @param array  $meta_args Array of key => value meta args.
	 * @param string $relation AND / OR relation between values.
	 */
	public static function get_one_by_meta( array $meta_args, string $relation = 'AND' ): ?WP_Post {
		$args = array_merge(
			self::get_default_args(),
			[
				'posts_per_page' => 1,
				'relation'       => $relation,
			]
		);

		foreach ( $meta_args as $key => $value ) {
			$args['meta_query'][] = [
				'key'   => $key,
				'value' => $value,
			];
		}

		$posts = get_posts( $args );
		if ( $posts ) {
			return $posts[0];
		}

		return null;
	}
	/**
	 * Gets posts by simple key => value args.
	 *
	 * @param array  $meta_args Array of key => value meta args.
	 * @param string $relation AND / OR relation between values.
	 */
	public static function get_by_meta( array $meta_args, string $relation = 'AND' ): array {
		$args = array_merge(
			self::get_default_args(),
			[
				'posts_per_page' => -1,
				'relation'       => $relation,
			]
		);

		foreach ( $meta_args as $key => $value ) {
			$args['meta_query'][] = [
				'key'   => $key,
				'value' => $value,
			];
		}

		return get_posts( $args );
	}

	/**
	 * Pass a more advanced meta query.
	 *
	 * @param array  $meta_args Meta args @see https://developer.wordpress.org/reference/classes/wp_meta_query/#accepted-arguments.
	 * @param string $relation AND / OR relation between values.
	 */
	public static function get_by_meta_query( array $meta_args = [], string $relation = 'AND' ): ?array {

		$args = array_merge(
			self::get_default_args(),
			[
				'posts_per_page' => -1,
				'meta_query'     => [
					'relation' => $relation,
					$meta_args,
				],
			]
		);

		return get_posts( $args );
	}

	/**
	 * Returns all posts between specified dates.
	 *
	 * @param string $start Start date.
	 * @param string $end End date.
	 */
	public static function get_all_between( string $start, string $end ): array {
		$args = array_merge(
			self::get_default_args(),
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

		return get_posts( $args );
	}

	/**
	 * Create or update a post.
	 *
	 * @param array $post_data Array of post fields @see https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters.
	 * @param array $post_meta Array of meta fields.
	 * @param bool  $wp_error Whether to return a WP_Error on failure.
	 */
	public static function save( array $post_data = [], array $post_meta = [], bool $wp_error = false ): ?WP_Post {

		// Merge incoming arguments with defaults.
		$post_data = array_merge( self::get_default_args(), $post_data );

		if ( isset( $post_data['ID'] ) && $post_data['ID'] > 0 ) {
			// Update post.
			$post_id = wp_update_post( $post_data );
		} else {
			// Create new post.
			$post_id = wp_insert_post( $post_data, $wp_error );
		}

		return self::update_meta( $post_id, $post_meta );
	}

	/**
	 * Update the meta.
	 *
	 * @param int|WP_Error $post_id Post ID.
	 * @param array        $post_meta Post meta.
	 */
	public static function update_meta( $post_id, array $post_meta ): ?WP_Post {
		// If something went wrong, return null.
		if ( 0 === $post_id || $post_id instanceof WP_Error ) {
			return null;
		}

		foreach ( $post_meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return get_post( $post_id );
	}
}
