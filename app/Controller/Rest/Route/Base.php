<?php

namespace Kudos\Controller\Rest\Route;

abstract class Base {

	/**
	 * Route namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'kudos/v1';

	/**
	 * Base route defined in child.
	 *
	 * @var string
	 */
	protected $base;

	/**
	 * Returns the REST namespace.
	 *
	 * @return string
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

		return '/' . $this->base;

	}

	/**
	 * Get all routes. Should be specified in child class.
	 */
	abstract public function get_routes(): array;

	/**
	 * Called to register all the routes defined in this service.
	 */
	public function register() {

		foreach ( $this->get_routes() as $route => $args ) {
			register_rest_route( $this->get_namespace(), $route, $args );
		}
	}

}