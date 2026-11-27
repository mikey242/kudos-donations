<?php
/**
 * Abstract rest controller.
 *
 * @link https://code.iseard.media/michael/kudos-donations
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Service\LicenceService;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;

abstract class BaseRestController extends AbstractRegistrable {

	protected string $namespace = 'kudos/v1';
	protected string $rest_base;

	/**
	 * Returns the REST namespace.
	 */
	protected function get_namespace(): string {
		return $this->namespace;
	}

	/**
	 * Gets the rest base route.
	 *
	 * @return string $rest_base
	 */
	protected function get_base(): string {
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
		add_filter( 'rest_post_dispatch', [ self::class, 'prevent_caching' ], 10, 3 );
	}

	/**
	 * Marks Kudos REST responses as uncacheable. WordPress only does this for logged-in users, so a
	 * cache that stores REST responses could otherwise keep serving a donor the first payment status
	 * ("open") while the return page polls for "paid".
	 *
	 * @param mixed           $response The response being sent.
	 * @param mixed           $_server  The REST server.
	 * @param WP_REST_Request $request  The request being answered.
	 * @return mixed
	 */
	public static function prevent_caching( $response, $_server, WP_REST_Request $request ) {
		if ( $response instanceof WP_HTTP_Response && str_starts_with( $request->get_route(), '/kudos/' ) ) {
			foreach ( wp_get_nocache_headers() as $name => $value ) {
				if ( $value ) {
					$response->header( $name, $value );
				}
			}
		}

		return $response;
	}

	/**
	 * Registers the routes defined in get_routes.
	 */
	protected function register_routes(): void {
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
	 * Returns true if licence is active and user has admin access, or a WP_Error describing why not.
	 *
	 * @return true|WP_Error
	 */
	public function is_licence_active_and_admin() {
		if ( ! $this->can_manage_options() ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to perform this action.', 'kudos-donations' ),
				[ 'status' => 403 ]
			);
		}

		if ( ! LicenceService::is_active() ) {
			return new WP_Error(
				'rest_licence_inactive',
				__( 'An active licence is required to perform this action.', 'kudos-donations' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Checks the provided honeypot field to detect bots.
	 *
	 * @param array $values Array of form values.
	 */
	protected function is_bot( array $values ): bool {
		// Seconds the form was open before submitting, measured in the browser so clock differences don't matter.
		if ( isset( $values['elapsed'] ) && (int) $values['elapsed'] < 4 ) {
			return true;
		}

		// Check if honeypot field completed.
		if ( ! empty( $values['donation'] ) ) {
			return true;
		}

		return false;
	}
}
