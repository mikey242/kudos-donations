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
use IseardMedia\Kudos\Enum\FieldType;
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
	 * Mail service routes.
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
		$migrations = $this->migration->get_migrations();
		$history    = (array) get_option( MigrationHandler::SETTING_MIGRATION_HISTORY, [] );

		if ( empty( $migrations ) ) {
			return new WP_REST_Response(
				[
					'success' => true,
				],
				200
			);
		}

		$pending_migrations = array_filter(
			$migrations,
			function ( $migration ) use ( $history ) {
				return ! \in_array( $migration->get_version(), $history, true );
			}
		);

		foreach ( $pending_migrations as $migration ) {
			foreach ( $migration->get_jobs() as $job_name => $job_details ) {
				if ( $migration->is_complete( $job_name ) ) {
					continue;
				}

				$migration->run( $job_name );

				return new WP_REST_Response(
					[
						'success'  => true,
						'progress' => [
							'version'  => $migration->get_version(),
							'job'      => $job_details['label'] ?? $job_name,
							'complete' => $migration->is_complete( $job_name ),
							'offset'   => $migration->get_offset( $job_name ),
						],
					]
				);

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
