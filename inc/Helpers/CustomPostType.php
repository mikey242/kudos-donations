<?php

namespace IseardMedia\Kudos\Helpers;

use Exception;
use WP_Query;

class CustomPostType
{
    private $post_type;
    /**
     * @var array
     */
    private $args;
    /**
     * @var array
     */
    private $custom_meta;

    /**
     * The custom post type constructor.
     *
     * @param $post_type
     * @param array $args
     * @param array $custom_meta
     */
    public function __construct($post_type, array $args, array $custom_meta)
    {
        $this->post_type   = $post_type;
        $this->args        = $args;
        $this->custom_meta = $custom_meta;
    }

	/**
	 * Gets the custom post type with post meta by id.
	 *
	 * @param string $value
	 *
	 * @return array|null
	 * @throws Exception
	 */
    public static function get_post(string $value): ?array
    {
        $post = get_post($value);

        if ($post) {
            $postMeta = get_post_meta($post->ID);
            if ($postMeta) {
                $postMeta['name'] = $post->post_title;

                return $postMeta;
            }
        }

        /* translators: %s: Post id */
        throw new Exception(sprintf(__('Post with id "%s" not found.', 'kudos-donations'), $value));
    }
}
