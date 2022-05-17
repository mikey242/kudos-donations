<?php

namespace Kudos\Helpers;

use Exception;

class CustomPostType {

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
	public function __construct( $post_type, array $args, array $custom_meta ) {
		$this->post_type   = $post_type;
		$this->args        = $args;
		$this->custom_meta = $custom_meta;

		$this->register_post();
		$this->register_meta();
	}

	/**
	 * Gets the custom post type with post meta by id.
	 *
	 * @param string|null $value
	 *
	 * @return array|null
	 * @throws Exception
	 */
	public static function get_post( string $value ): ?array {

		$post = get_post( $value );

		if ( $post ) {
			$postMeta = get_post_meta( $post->ID );
			if ( $postMeta ) {
				$postMeta['name'] = $post->post_title;

				return $postMeta;
			}
		}

		/* translators: %s: Campaign id */
		throw new Exception( sprintf( __( 'Campaign "%s" not found.', 'kudos-donations' ), $value ) );
	}

	/**
	 * Register the post type with WordPress.
	 *
	 * @return void
	 */
	private function register_post() {
		$args = wp_parse_args( $this->args, [
			'public'       => false,
			'show_in_rest' => true,
			'supports'     => [ 'title', 'custom-fields' ],
		] );
		register_post_type( $this->post_type, $args );
	}

	/**
	 * Register the post meta.
	 *
	 * @return void
	 */
	private function register_meta() {
		foreach ( $this->custom_meta as $key => $value ) {
			$args = wp_parse_args( $value, [
				'show_in_rest' => true,
				'single'       => true,
			] );
			register_post_meta( $this->post_type, $key, $args );
		}
	}
}