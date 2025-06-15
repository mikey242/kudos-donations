<?php
/**
 * Migration Rest Routes.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use IseardMedia\Kudos\Service\MigrationService;
use IseardMedia\Kudos\Service\NoticeService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Migration extends AbstractRestController {

	/**
	 * Migration handler.
	 *
	 * @var MigrationService
	 */
	private MigrationService $migration;

	/**
	 * Migration constructor.
	 *
	 * @param MigrationService $migration Migration handler.
	 */
	public function __construct( MigrationService $migration ) {
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
	 * @return WP_REST_Response | WP_Error
	 */
	public function rest_migrate_handler( WP_REST_Request $request ) {
		$batch_size = (int) $request->get_param( 'batch_size' );
		$offset     = (int) $request->get_param( 'offset' );

		$migrations       = \array_slice( $this->migration->get_migrations(), $offset, $batch_size );
		$total_migrations = \count( $this->migration->get_migrations() );

		if ( empty( $migrations ) ) {
			return new WP_REST_Response(
				[
					'completed'   => true,
					'next_offset' => $offset,
				],
				200
			);
		}

		update_option( MigrationService::SETTING_MIGRATION_BUSY, true );

		foreach ( $migrations as $migration ) {
			if ( ! $this->run_migration( $migration ) ) {
				update_option( MigrationService::SETTING_MIGRATION_BUSY, false );
				NoticeService::add_notice( __( 'Migration step failed. Please check the logs.', 'kudos-donations' ), NoticeService::ERROR );
				return new WP_Error( 'migration_failed', __( 'Migration failed.', 'kudos-donations' ) );
			}

			// Stop here if this migration isn't yet done — keep offset the same.
			if ( ! $migration->is_complete() ) {
				update_option( MigrationService::SETTING_MIGRATION_BUSY, false );
				return new WP_REST_Response(
					[
						'completed'   => false,
						'next_offset' => $offset,
						'migration'   => \get_class( $migration ),
						'progress'    => $migration->get_progress_summary(),
					],
					200
				);
			}
		}

		$next_offset = $offset + $batch_size;
		$completed   = $next_offset >= $total_migrations;

		if ( $completed ) {
			update_option( MigrationService::SETTING_DB_VERSION, KUDOS_DB_VERSION );
			update_option( MigrationService::SETTING_MIGRATION_BUSY, false );
			NoticeService::add_notice( __( 'Migrations completed successfully.', 'kudos-donations' ), NoticeService::SUCCESS, true, 'kudos-migration-complete' );
		}

		return new WP_REST_Response(
			[
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
	private function run_migration( MigrationInterface $migration ): bool {
		$version = $migration->get_version();
		$history = get_option( MigrationService::SETTING_MIGRATION_HISTORY, [] );

		if ( \in_array( $version, $history, true ) ) {
			$this->logger->debug( 'Migration already applied, skipping', [ 'migration' => $version ] );
			return true;
		}

		$this->logger->info( 'Running migration step: ' . $version );

		if ( ! $migration->step() ) {
			$this->logger->error( 'Migration step failed.', [ 'migration' => $version ] );
			return false;
		}

		// Still not complete? That's fine — we'll continue on next request.
		if ( ! $migration->is_complete() ) {
			$this->logger->info( 'Migration not yet complete, more steps required.', [ 'migration' => $version ] );
			return true;
		}

		// Mark as complete if it's now finished.
		$this->logger->info( 'Migration complete: ' . $version );
		$history[] = $version;
		update_option( MigrationService::SETTING_MIGRATION_HISTORY, $history );

		return true;
	}
}
