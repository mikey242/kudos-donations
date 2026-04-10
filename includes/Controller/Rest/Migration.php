<?php
/**
 * Migration Rest Routes.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Container\Handler\MigrationHandler;
use WP_REST_Response;
use WP_REST_Server;

class Migration extends BaseRestController {

	protected string $rest_base = 'migration';

	/**
	 * Migration handler.
	 *
	 * @var MigrationHandler
	 */
	private MigrationHandler $migration;

	/**
	 * Migration constructor.
	 *
	 * @param MigrationHandler $migration Migration handler.
	 */
	public function __construct( MigrationHandler $migration ) {
		$this->migration = $migration;
	}

	/**
	 * Migration routes.
	 */
	public function get_routes(): array {
		return [
			'/should-upgrade' => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'should_upgrade' ],
				'permission_callback' => [ $this, 'can_manage_options' ],

			],
			'/run'            => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'run_migrations' ],
				'permission_callback' => [ $this, 'can_manage_options' ],
			],
		];
	}

	/**
	 * Check whether there are pending migrations
	 */
	public function should_upgrade(): WP_REST_Response {
		return new WP_REST_Response( $this->migration->should_upgrade() );
	}

	/**
	 * Handles a request for running migrations.
	 */
	public function run_migrations(): WP_REST_Response {
		$result = $this->migration->run_migration_batch();

		if ( null === $result ) {
			return new WP_REST_Response(
				[
					'success' => true,
					'done'    => true,
				],
				200
			);
		}

		return new WP_REST_Response(
			[
				'success'  => true,
				'progress' => $result,
			],
			200
		);
	}
}
