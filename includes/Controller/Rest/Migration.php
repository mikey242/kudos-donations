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
			$version       = $migration->get_version();
			$transient_key = '_kudos_migration_' . $version . '_last_job';
			$last_job      = get_transient( $transient_key );
			$jobs          = $migration->get_jobs();
			$job_names     = array_keys( $jobs );

			// Resume from the last job if we have one stored.
			if ( false !== $last_job ) {
				$last_index = array_search( $last_job, $job_names, true );
				if ( false !== $last_index ) {
					$job_names = \array_slice( $job_names, $last_index );
				}
			}

			foreach ( $job_names as $job_name ) {
				$processed = $migration->run( $job_name );

				if ( $processed > 0 ) {
					// Done. Update the transient with the completed job name.
					set_transient( $transient_key, $job_name, HOUR_IN_SECONDS );

					return new WP_REST_Response(
						[
							'success'  => true,
							'progress' => [
								'version'   => $version,
								'job'       => $jobs[ $job_name ]['label'] ?? $job_name,
								'processed' => $processed,
							],
						],
						200
					);
				}
			}

			delete_transient( $transient_key );
			$history[] = $version;

			// Migration complete, add to migration history.
			update_option( MigrationHandler::SETTING_MIGRATION_HISTORY, $history );
		}

		// All migrations complete, update database version.
		update_option( MigrationHandler::SETTING_DB_VERSION, KUDOS_DB_VERSION );

		return new WP_REST_Response(
			[
				'success'  => true,
				'progress' => [],
				'done'     => true,
			],
			200
		);
	}
}
