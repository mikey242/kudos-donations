<?php
/**
 * Abstract rest controller.
 *
 * @link https://github.com/mikey242/kudos-donations
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Container\AbstractRegistrable;

abstract class BaseRestController extends AbstractRegistrable {

	protected string $namespace = 'kudos/v1';
	protected string $rest_base;

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
		return $this->rest_base;
	}

	/**
	 * Get all routes. Should be specified in child class.
	 */
	abstract public function get_routes(): array;

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		$this->register_routes();
	}

	/**
	 * Registers the routes defined in get_routes.
	 */
	public function register_routes(): void {
		foreach ( $this->get_routes() as $route => $args ) {
			register_rest_route( $this->get_namespace(), $this->get_base() . $route, $args );
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
	public static function get_registration_action(): string {
		return 'rest_api_init';
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

	/**
	 * Checks the provided honeypot field to detect bots.
	 *
	 * @param array $values Array of form values.
	 */
	public function is_bot( array $values ): bool {
		// Check if timestamp is present and tabs completed too quickly.
		if ( isset( $values['timestamp'] ) && abs( (int) $values['timestamp'] - time() ) < 4 ) {
			return true;
		}

		// Check if honeypot field completed.
		if ( ! empty( $values['donation'] ) ) {
			return true;
		}

		return false;
	}
}
