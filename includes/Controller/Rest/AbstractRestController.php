<?php
/**
 * Abstract rest controller.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Container\Delayed;
use IseardMedia\Kudos\Container\Registrable;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use WP_Error;
use WP_REST_Controller;

abstract class AbstractRestController extends WP_REST_Controller implements Registrable, Delayed, LoggerAwareInterface {

	use LoggerAwareTrait;

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
	 * {@inheritDoc}
	 */
	public function register_routes(): void {
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
	 * Checks the provided honeypot field and logs request if bot detected.
	 *
	 * @param array $values Array of form value.
	 */
	public function is_bot( array $values ): bool {
		$time_diff = abs( $values['timestamp'] - time() );

		// Check if tabs completed too quickly.
		if ( $time_diff < 4 ) {
			new WP_Error(
				'rest_forbidden',
				__( 'Bot detected, rejecting tabs.', 'kudos-donations' ),
				[
					'reason'     => 'FormTab completed too quickly',
					'time_taken' => $time_diff,
				]
			);

			return true;
		}

		// Check if honeypot field completed.
		if ( ! empty( $values['donation'] ) ) {
			new WP_Error(
				'rest_forbidden',
				__( 'Bot detected, rejecting tabs.', 'kudos-donations' ),
				array_merge(
					[
						'reason' => 'Honeypot field completed',
					],
					$values
				)
			);

			return true;
		}

		return false;
	}
}
