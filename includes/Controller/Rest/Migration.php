<?php
/**
 * Migration Rest Routes.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Container\Handler\MigrationHandler;
use WP_REST_Response;
use WP_REST_Server;

class Migration extends BaseRestController {

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
		$this->rest_base = 'migration';
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
				'callback'            => [ $this, 'rest_migrate_handler' ],
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
	public function rest_migrate_handler(): WP_REST_Response {
		$pending_migrations = $this->migration->get_pending_migrations();

		if ( empty( $pending_migrations ) ) {
			return new WP_REST_Response(
				[
					'success' => true,
				],
				200
			);
		}

		$history = (array) get_option( MigrationHandler::SETTING_MIGRATION_HISTORY, [] );

		foreach ( $pending_migrations as $migration ) {
			foreach ( $migration->get_jobs() as $job_name => $job_details ) {
				$processed = $migration->run( $job_name );

				if ( $processed > 0 ) {
					return new WP_REST_Response(
						[
							'success'  => true,
							'progress' => [
								'version' => $migration->get_version(),
								'job'     => $job_details['label'] ?? $job_name,
							],
						]
					);
				}
			}
			$history[] = $migration->get_version();
			update_option( MigrationHandler::SETTING_MIGRATION_HISTORY, $history );
		}

		update_option( MigrationHandler::SETTING_DB_VERSION, KUDOS_DB_VERSION );

		return new WP_REST_Response(
			[
				'success'  => true,
				'progress' => [],
				'done'     => true,
			]
		);
	}
}
