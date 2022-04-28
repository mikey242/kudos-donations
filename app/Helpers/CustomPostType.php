<?php

namespace Kudos\Helpers;

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

	public function __construct( $post_type, $args = [], $custom_meta = [] ) {
		$this->post_type   = $post_type;
		$this->args        = $args;
		$this->custom_meta = $custom_meta;

		$this->register_post();
	}

	private function register_post() {
		$args = wp_parse_args( $this->args, [
			'public'       => false,
			'show_in_rest' => true,
			'supports'     => [ 'title', 'custom-fields' ],
		] );
		register_post_type( $this->post_type, $args );
		$this->register_meta();
	}

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