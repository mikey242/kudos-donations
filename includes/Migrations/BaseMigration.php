<?php
/**
 * BaseMigration class.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2025 Iseard Media
 */

declare( strict_types=1 );

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Container\SafeLoggerTrait;
use Psr\Log\LoggerAwareInterface;

abstract class BaseMigration implements MigrationInterface, LoggerAwareInterface {

	use SafeLoggerTrait;

	protected const DEFAULT_CHUNK_SIZE = 50;
	protected const OFFSET_KEY         = 'offset';
	protected const COMPLETE_KEY       = 'complete';

	protected string $version;

	/**
	 * Runs a single job for this migration in a batch.
	 *
	 * @param string $job The job name (as defined in get_jobs()).
	 * @return bool True if the job ran successfully, false otherwise.
	 */
	public function run( string $job ): bool {
		$jobs = $this->get_jobs();

		if ( ! isset( $jobs[ $job ] ) ) {
			$this->logger->warning( "Migration job '$job' is not defined or not callable." );
			return false;
		}

		if ( $this->is_complete( $job ) ) {
			$this->logger->info( "Migration job '$job' is already complete." );
			return false;
		}

		$offset   = $this->get_offset( $job );
		$callback = $jobs[ $job ]['callback'];
		$limit    = static::DEFAULT_CHUNK_SIZE;

		try {
			// Expect the callback to return the number of items processed.
			$processed = (int) \call_user_func( $callback, $offset, $limit );

			if ( $processed < $limit ) {
				$this->mark_complete( $job );
				$this->logger->info( "Migration job '$job' completed." );
			} else {
				$this->set_offset( $job, $offset + $limit );
				$this->logger->info( "Migration job '$job' processed $processed items from offset $offset." );
			}

			return true;
		} catch ( \Throwable $e ) {
			$this->logger->error( "Migration job '$job' failed: " . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Returns the version of the migration.
	 */
	public function get_version(): string {
		return $this->version;
	}

	/**
	 * Returns an array of job function names.
	 *
	 * @return array<string, array{callback: callable, label?: string}>
	 */
	abstract public function get_jobs(): array;

	/**
	 * Defines a job.
	 *
	 * @param callable    $callback The callback to run.
	 * @param string|null $label The label to display to the front-end.
	 * @return array{callback: callable, label?: string}
	 */
	protected function job( callable $callback, ?string $label = null ): array {
		return compact( 'callback', 'label' );
	}

	/**
	 * Builds a unique option key used to store the state of a job.
	 *
	 * @param string $job The job name.
	 */
	protected function get_option_key( string $job ): string {
		return "_kudos_migration_{$this->version}_{$job}_state";
	}

	/**
	 * Retrieves the current offset for a given job.
	 *
	 * @param string $job The job method name.
	 */
	public function get_offset( string $job ): int {
		$state = get_option( $this->get_option_key( $job ), [] );
		return isset( $state[ self::OFFSET_KEY ] ) ? (int) $state[ self::OFFSET_KEY ] : 0;
	}

	/**
	 * Updates the offset for a given job.
	 *
	 * @param string $job The job method name.
	 * @param int    $offset The new offset.
	 */
	protected function set_offset( string $job, int $offset ): void {
		$state                       = get_option( $this->get_option_key( $job ), [] );
		$state[ self::OFFSET_KEY ]   = $offset;
		$state[ self::COMPLETE_KEY ] = false;
		update_option( $this->get_option_key( $job ), $state );
	}

	/**
	 * Marks the current job as completed.
	 *
	 * @param string $job The job method name.
	 */
	protected function mark_complete( string $job ): void {
		update_option(
			$this->get_option_key( $job ),
			[
				self::OFFSET_KEY   => 0,
				self::COMPLETE_KEY => true,
			]
		);
	}

	/**
	 * Checks if migration job is complete.
	 *
	 * @param string $job The job method name.
	 */
	public function is_complete( string $job ): bool {
		$state = get_option( $this->get_option_key( $job ), [] );
		return ! empty( $state[ self::COMPLETE_KEY ] );
	}

	/**
	 * Clears the stored state for a job.
	 *
	 * @param string $job The job method name.
	 */
	protected function clear_job_state( string $job ): void {
		delete_option( $this->get_option_key( $job ) );
	}
}
