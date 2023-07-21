<?php

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Infrastructure\Delayed;
use IseardMedia\Kudos\Infrastructure\Registrable;
use WP_REST_Controller;

abstract class AbstractRestController extends WP_REST_Controller implements Registrable, Delayed {


	/**
	 * Configure the namespace for the plugin.
	 */
	public function __construct() {
		$this->namespace = 'kudos/v1';
	}

	/**
	 * Returns the REST namespace.
	 */
	public function get_namespace(): string {
		return $this->namespace;
	}

	/**
	 * Gets the rest base route.
	 *
	 * @return string $rest_base
	 */
	public function get_base(): string {
		return '/' . $this->rest_base;
	}

	/**
	 * Get all routes. Should be specified in child class.
	 */
	abstract public function get_routes(): array;

	/**
	 * Called to register all the routes defined in this service.
	 */
	public function register(): void {
		foreach ( $this->get_routes() as $route => $args ) {
			register_rest_route( static::get_namespace(), static::get_base() . $route, $args );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_action_priority(): int {
		return 10;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_registration_actions(): array {
		return [ 'rest_api_init' ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_enabled(): bool {
		return true;
	}

	/**
	 * To be used as permission callback.
	 */
	public function can_manage_options(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * To be used as permission callback.
	 */
	public function can_edit_posts(): bool {
		return current_user_can( 'edit_posts' );
	}
}
