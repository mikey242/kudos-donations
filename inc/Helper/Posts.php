<?php

namespace IseardMedia\Kudos\Helper;

class Posts {


	public static function get_shortcodes_on_page( $post_id, $shortcode ): bool|array {
		// Get the post content once.
		$content = get_the_content( null, false, $post_id );

		// Double check that there is content.
		if ( $content ) {
			// Shortcode regex.
			$shortcode_regex = '/\[' . $shortcode . '\s.*?]/';

			// Get all the shortcodes from the page.
			if ( preg_match_all( $shortcode_regex, $content, $shortcodes ) ) {
				// Store them here.
				$final_array = [];

				// Extract the attributes from the shortcode.
				foreach ( $shortcodes[0] as $s ) {
					$attributes = self::shortcode_parse_atts( $s );

					// The return the post.
					$final_array[] = $attributes;
				}

				// Return the array.
				$results = $final_array;
				// Otherwise return an empty array if none are found.
			} else {
				$results = [];
			}

			// Return it.
			return $results;
		} else {
			return false;
		}
	}

	public static function shortcode_parse_atts( $shortcode ): array {
		// Store the shortcode attributes in an array here.
		$attributes = [];

		if ( preg_match_all( '/\w+\=\".*?\"/', $shortcode, $key_value_pairs ) ) {
			// Now split up the key value pairs.
			foreach ( $key_value_pairs[0] as $kvp ) {
				$kvp                    = str_replace( '"', '', $kvp );
				$pair                   = explode( '=', $kvp );
				$attributes[ $pair[0] ] = $pair[1];
			}
		}

		// Return the array.
		return $attributes;
	}
}
