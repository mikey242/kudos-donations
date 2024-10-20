<?php
/**
 * Migration Rest Routes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2023 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Container\Handler\MigrationHandler;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Migration extends AbstractRestController {

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
		parent::__construct();

		$this->rest_base = 'migration';
		$this->migration = $migration;
	}

	/**
	 * Mail service routes.
	 */
	public function get_routes(): array {
		return [
			'/migrate' => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'rest_migrate_handler' ],
				'args'                => [
					'batch_size' => [
						'type'     => FieldType::INTEGER,
						'default'  => 1,
						'required' => true,
					],
					'offset'     => [
						'type'     => FieldType::INTEGER,
						'default'  => 0,
						'required' => true,
					],
				],
				'permission_callback' => [ $this, 'can_manage_options' ],
			],
		];
	}

	/**
	 * Handles a request for running migrations.
	 *
	 * @param WP_REST_Request $request Request array.
	 */
	public function rest_migrate_handler( WP_REST_Request $request ): WP_REST_Response {
		$batch_size = $request->get_param( 'batch_size' );
		$offset     = $request->get_param( 'offset' );
		$migrations = \array_slice( $this->migration->get_migrations(), $offset, $batch_size );

		// Process the migrations in this batch.
		foreach ( $migrations as $migration ) {
			if ( ! $this->run_migration( $migration ) ) {
				return new WP_REST_Response(
					[
						'success' => false,
						'message' => 'Migration failed',
					],
					500
				);
			}
		}

		$next_offset = $offset + \count( $migrations );
		$completed   = $next_offset >= \count( $this->migration->get_migrations() );

		update_option( MigrationHandler::SETTING_DB_VERSION, KUDOS_DB_VERSION );

		return new WP_REST_Response(
			[
				'success'     => true,
				'completed'   => $completed,
				'next_offset' => $next_offset,
			],
			200
		);
	}

	/**
	 * Run the supplied migration.
	 *
	 * @param MigrationInterface $migration The migration to run.
	 */
	public function run_migration( MigrationInterface $migration ): bool {

		// Prevent running migration if already in history.
		if ( \in_array( $migration->get_version(), get_option( MigrationHandler::SETTING_MIGRATION_HISTORY, [] ), true ) ) {
			$this->logger->debug( 'Migration already applied, skipping', [ 'migration' => $migration->get_version() ] );
			return true;
		}

		$this->logger->debug( 'Running migration: ' . $migration->get_version() );

		// Run migration and stop if not successful.
		if ( ! $migration->run() ) {
			$this->logger->error( 'Migration failed.', [ 'migration' => $migration->get_version() ] );
			return false;
		}

		// Update migration history.
		$migration_history   = get_option( MigrationHandler::SETTING_MIGRATION_HISTORY, [] );
		$migration_history[] = $migration->get_version();
		update_option( MigrationHandler::SETTING_MIGRATION_HISTORY, $migration_history );

		return true;
	}
}
