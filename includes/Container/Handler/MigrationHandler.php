<?php
/**
 * Repository handler.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Container\Handler;

use IseardMedia\Kudos\Container\AbstractRegistrable;
use IseardMedia\Kudos\Container\HasSettingsInterface;
use IseardMedia\Kudos\Enum\FieldType;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use IseardMedia\Kudos\Service\NoticeService;

class MigrationHandler extends AbstractRegistrable implements HasSettingsInterface {

	public const SETTING_DB_VERSION        = '_kudos_db_version';
	public const SETTING_MIGRATION_HISTORY = '_kudos_migration_history';
	public const SETTING_PLUGIN_VERSION    = '_kudos_donations_version';

	/**
	 * Action Scheduler hook name for background auto-migration batches.
	 */
	public const AUTO_MIGRATION_HOOK = 'kudos_run_auto_migration_batch';

	/**
	 * Option that stores the DB version for which the auto migration has
	 * already completed, preventing repeated cron scheduling.
	 */
	private const AUTO_DONE_OPTION = '_kudos_auto_migration_done';

	/**
	 * Array of migrations.
	 *
	 * @var MigrationInterface[]
	 */
	protected array $migrations = [];

	/**
	 * MigrationManager constructor.
	 *
	 * @param iterable $migrations Migrations are injected here by the container.
	 */
	public function __construct( iterable $migrations ) {
		foreach ( $migrations as $migration ) {
			$this->add( $migration );
		}
		usort(
			$this->migrations,
			fn( MigrationInterface $a, MigrationInterface $b ) =>
			version_compare( $a->get_version(), $b->get_version() )
		);
	}

	/**
	 * Add service to list.
	 *
	 * @param MigrationInterface $migration Service.
	 */
	public function add( MigrationInterface $migration ): void {
		$this->migrations[] = $migration;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Runs on 'init' (the base-class default) so the cron callback is
	 * registered even during WP-Cron requests, which do not fire admin_init.
	 */
	public function register(): void {
		add_action( self::AUTO_MIGRATION_HOOK, [ $this, 'run_auto_migration_batch' ] );

		if ( $this->should_upgrade() ) {
			$this->add_localized_data();
			// Enqueue a background batch if auto-migratable jobs have not yet.
			if ( get_option( self::AUTO_DONE_OPTION ) !== KUDOS_DB_VERSION ) {
				Utils::enqueue_async_action( self::AUTO_MIGRATION_HOOK );
			}

			if ( ! Utils::is_kudos_admin() ) {
				$this->add_migration_notice();
			}
		}
	}

	/**
	 * Adds needsUpgrade to localized script data.
	 */
	private function add_localized_data(): void {
		add_filter(
			'kudos_admin_localization',
			function ( array $data ): array {
				$data['needsUpgrade'] = $this->should_upgrade();
				return $data;
			}
		);
	}

	/**
	 * Determines if a migration should be run.
	 */
	public function should_upgrade(): bool {
		$db_version = get_option( self::SETTING_DB_VERSION );
		return $db_version && version_compare( $db_version, KUDOS_DB_VERSION, '<' );
	}

	/**
	 * Returns currently stored migrations.
	 *
	 * @return MigrationInterface[]
	 */
	public function get_migrations(): array {
		return $this->migrations;
	}

	/**
	 * Returns migrations that have not yet been run.
	 *
	 * @return MigrationInterface[]
	 */
	public function get_pending_migrations(): array {
		$history = (array) get_option( self::SETTING_MIGRATION_HISTORY, [] );

		return array_filter(
			$this->migrations,
			fn( MigrationInterface $migration ) => ! \in_array( $migration->get_version(), $history, true )
		);
	}

	/**
	 * Returns the transient key used to track the last completed job for a migration version.
	 *
	 * @param string $version The migration version.
	 */
	private function get_transient_key( string $version ): string {
		return '_kudos_migration_' . $version . '_last_job';
	}

	/**
	 * Slices a job list to resume from the last recorded job.
	 * If the stored job is not found in the list, the full list is returned.
	 *
	 * @param string[] $job_names     Ordered job names to run.
	 * @param string   $transient_key Transient holding the last completed job name.
	 * @return string[]
	 */
	private function resume_jobs( array $job_names, string $transient_key ): array {
		$last_job   = get_transient( $transient_key );
		$last_index = false !== $last_job ? array_search( $last_job, $job_names, true ) : false;
		return false !== $last_index ? \array_slice( $job_names, $last_index ) : $job_names;
	}

	/**
	 * Runs one batch of the auto-migratable jobs only (no user confirmation required).
	 *
	 * Does NOT mark the migration complete in history or update the DB version —
	 * that is reserved for the full manual migration so the admin backup prompt
	 * still applies to the remaining data (donors, transactions, subscriptions…).
	 *
	 * @return bool True when a batch was processed and more work may remain,
	 *              false when all auto jobs are done (or there were none).
	 */
	private function process_auto_batch(): bool {
		foreach ( $this->get_pending_migrations() as $migration ) {
			$jobs      = $migration->get_jobs();
			$auto_jobs = array_keys( array_filter( $jobs, fn( $job ) => $job['auto'] ?? false ) );

			if ( empty( $auto_jobs ) ) {
				continue;
			}

			$transient_key = $this->get_transient_key( $migration->get_version() );
			$job_names     = $this->resume_jobs( $auto_jobs, $transient_key );

			foreach ( $job_names as $job_name ) {
				$processed = $migration->run( $job_name );

				if ( $processed > 0 ) {
					set_transient( $transient_key, $job_name, HOUR_IN_SECONDS );
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * WP-Cron callback: runs one auto-migration batch and reschedules if more remain.
	 * Records completion so register() stops scheduling once all auto jobs are done.
	 */
	public function run_auto_migration_batch(): void {
		if ( $this->process_auto_batch() ) {
			Utils::enqueue_async_action( self::AUTO_MIGRATION_HOOK );
		} else {
			update_option( self::AUTO_DONE_OPTION, KUDOS_DB_VERSION );
		}
	}

	/**
	 * Runs one full migration batch (all jobs, requires user confirmation).
	 *
	 * Marks each migration complete in history and updates the DB version
	 * when all pending migrations finish.
	 *
	 * @return array{version: string, job: string, processed: int}|null
	 *         Progress array while work remains, null when complete (or nothing pending).
	 */
	public function run_migration_batch(): ?array {
		$pending_migrations = $this->get_pending_migrations();

		if ( empty( $pending_migrations ) ) {
			update_option( self::SETTING_DB_VERSION, KUDOS_DB_VERSION );
			return null;
		}

		$history = (array) get_option( self::SETTING_MIGRATION_HISTORY, [] );

		foreach ( $pending_migrations as $migration ) {
			$version       = $migration->get_version();
			$transient_key = $this->get_transient_key( $version );
			$jobs          = $migration->get_jobs();
			$job_names     = $this->resume_jobs( array_keys( $jobs ), $transient_key );

			foreach ( $job_names as $job_name ) {
				$processed = $migration->run( $job_name );

				if ( $processed > 0 ) {
					set_transient( $transient_key, $job_name, HOUR_IN_SECONDS );

					return [
						'version'   => $version,
						'job'       => $jobs[ $job_name ]['label'] ?? $job_name,
						'processed' => $processed,
					];
				}
			}

			delete_transient( $transient_key );
			$history[] = $version;
			update_option( self::SETTING_MIGRATION_HISTORY, $history );
		}

		update_option( self::SETTING_DB_VERSION, KUDOS_DB_VERSION );

		return null;
	}

	/**
	 * Creates an admin notice with update button.
	 */
	private function add_migration_notice(): void {
		$form  = '<div>';
		$form .= "<a href='" . admin_url( '?page=kudos-campaigns' ) . "' class='button button-primary'>";
		$form .= __( 'Visit dashboard', 'kudos-donations' );
		$form .= '</a>';
		$form .= '</div>';

		NoticeService::notice(
			'<p><strong>' . __( 'Kudos Donations needs to update your database before you can continue.', 'kudos-donations' ) . '</strong><br/>' . __( 'Please make sure you backup your data before proceeding.', 'kudos-donations' ) . '</p>' . $form,
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_settings(): array {
		return [
			self::SETTING_MIGRATION_HISTORY => [
				'type'         => FieldType::ARRAY,
				'show_in_rest' => false,
				'default'      => [],
			],
			self::SETTING_DB_VERSION        => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => KUDOS_DB_VERSION,
			],
			self::SETTING_PLUGIN_VERSION    => [
				'type'         => FieldType::STRING,
				'show_in_rest' => true,
				'default'      => KUDOS_VERSION,
			],
		];
	}
}
