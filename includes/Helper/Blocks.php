<?php
/**
 * Block helper functions.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

namespace IseardMedia\Kudos\Helper;

use WP_Block_Parser_Block;
use WP_Post;

class Blocks {

	/**
	 * Returns modified block content.
	 *
	 * @param WP_Post|int $post Post instance or post id.
	 * @param callable    $callback Callback function.
	 */
	public static function get_new_content( $post, callable $callback ): string {
		$post    = get_post( $post );
		$content = $post->post_content;

		if ( has_blocks( $post->post_content ) ) {
			$blocks        = parse_blocks( $post->post_content );
			$parsed_blocks = self::parse_blocks( $blocks, $callback );

			$content = serialize_blocks( $parsed_blocks );
		}

		return $content;
	}

	/**
	 * Parse block objects.
	 *
	 * @param array    $blocks Array of block objects.
	 * @param callable $callback Callback function.
	 * @return WP_Block_Parser_Block[]
	 */
	protected static function parse_blocks( array $blocks, callable $callback ): array {
		$all_blocks = [];

		foreach ( $blocks as $block ) {
			// Go into inner front and run this method recursively.
			if ( ! empty( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = self::parse_blocks( $block['innerBlocks'], $callback );
			}

			// Make sure that is a valid block (some block names may be NULL).
			if ( ! empty( $block['blockName'] ) ) {
				$all_blocks[] = $callback( $block ); // the magic is here...
				continue;
			}

			// Continuously create back the front array.
			$all_blocks[] = $block;
		}

		return $all_blocks;
	}
}
