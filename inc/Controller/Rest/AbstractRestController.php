<?php

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Infrastructure\Container\Delayed;
use IseardMedia\Kudos\Infrastructure\Container\Registrable;
use WP_REST_Controller;

abstract class AbstractRestController extends WP_REST_Controller implements Registrable, Delayed
{

	public function __construct() {
		$this->namespace = 'kudos/v1';
	}

    /**
     * Returns the REST namespace.
     *
     * @return string
     */
    public function get_namespace(): string
    {
        return $this->namespace;
    }

    /**
     * Gets the rest base route.
     *
     * @return string $rest_base
     */
    public function get_base(): string
    {
        return '/' . $this->rest_base;
    }

	public static function get_registration_action_priority(): int {
		return 10;
	}

	public static function get_registration_actions(): array {
		return ['rest_api_init'];
	}

	public function is_enabled(): bool {
		return true;
	}

	/**
     * Get all routes. Should be specified in child class.
     */
    abstract public function get_routes(): array;

    /**
     * Called to register all the routes defined in this service.
     */
    public function register(): void {
        foreach ($this->get_routes() as $route => $args) {
            register_rest_route(static::get_namespace(), static::get_base() . $route, $args);
        }
    }
}
