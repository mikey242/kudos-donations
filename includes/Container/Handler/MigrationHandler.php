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
use IseardMedia\Kudos\Helper\Localization;
use IseardMedia\Kudos\Helper\Utils;
use IseardMedia\Kudos\Migrations\MigrationInterface;
use IseardMedia\Kudos\Notice\Notice;
use IseardMedia\Kudos\Notice\NoticeManager;

class MigrationHandler extends AbstractRegistrable implements HasSettingsInterface {

	public const SETTING_DB_VERSION        = '_kudos_db_version';
	public const SETTING_MIGRATION_HISTORY = '_kudos_migration_history';
	public const SETTING_PLUGIN_VERSION    = '_kudos_donations_version';

	/**
	 * Action Scheduler hook name for background auto-migration batches.
	 */
	public const AUTO_MIGRATION_HOOK = 'kudos_run_auto_migration_batch';

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
			$this->migrations[] = $migration;
		}
		usort(
			$this->migrations,
			fn( MigrationInterface $a, MigrationInterface $b ) =>
			version_compare( $a->get_version(), $b->get_version() )
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * Runs on 'init' (the base-class default) so the cron callback is
	 * registered even during WP-Cron requests, which do not fire admin_init.
	 */
	public function register(): void {
		add_action( self::AUTO_MIGRATION_HOOK, [ $this, 'run_auto_migration_batch' ] );

		$pending = $this->get_pending_migrations();

		if ( empty( $pending ) ) {
			return;
		}

		$has_auto   = (bool) array_filter( $pending, fn( MigrationInterface $m ) => $m->is_auto() );
		$has_manual = (bool) array_filter( $pending, fn( MigrationInterface $m ) => ! $m->is_auto() );

		if ( $has_auto ) {
			Utils::enqueue_async_action( self::AUTO_MIGRATION_HOOK );
		}

		if ( $has_manual ) {
			Localization::add_admin( 'needsUpgrade', true );

			if ( ! Utils::is_kudos_admin() ) {
				$this->add_migration_notice();
			}
		}
	}

	/**
	 * Determines if there are pending migrations that require user confirmation.
	 * Auto migrations are excluded — they run silently in the background.
	 */
	public function should_upgrade(): bool {
		return (bool) array_filter(
			$this->get_pending_migrations(),
			fn( MigrationInterface $m ) => ! $m->is_auto()
		);
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
			fn( MigrationInterface $migration ) =>
				! \in_array( $migration->get_version(), $history, true ) &&
				version_compare( $migration->get_version(), (string) get_option( self::SETTING_DB_VERSION, '0' ), '>' )
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
	 * Action Scheduler callback: runs all jobs for each pending auto migration in one shot.
	 * Stops at the first non-auto pending migration (ordering barrier).
	 */
	public function run_auto_migration_batch(): void {
		$history = (array) get_option( self::SETTING_MIGRATION_HISTORY, [] );

		foreach ( $this->get_pending_migrations() as $migration ) {
			if ( ! $migration->is_auto() ) {
				break;
			}

			foreach ( array_keys( $migration->get_jobs() ) as $job_name ) {
				$migration->run( $job_name );
			}

			$history[] = $migration->get_version();
			$this->get_logger()->info( "Auto migration {$migration->get_version()} marked complete." );
		}

		update_option( self::SETTING_MIGRATION_HISTORY, $history );

		if ( empty( $this->get_pending_migrations() ) ) {
			update_option( self::SETTING_DB_VERSION, KUDOS_DB_VERSION );
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

		NoticeManager::notice(
			new Notice(
				'migration-needed',
				'<p><strong>' . __( 'Kudos Donations needs to update your database before you can continue.', 'kudos-donations' ) . '</strong><br/>' . __( 'Please make sure you backup your data before proceeding.', 'kudos-donations' ) . '</p>' . $form,
			)
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
