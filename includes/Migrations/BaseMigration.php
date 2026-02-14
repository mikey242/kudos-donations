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

	protected string $version;

	/**
	 * Runs a single job for this migration in a batch.
	 *
	 * @param string $job The job name (as defined in get_jobs()).
	 * @return int Number of items processed. 0 means job is complete.
	 */
	public function run( string $job ): int {
		$jobs = $this->get_jobs();

		$this->logger->info( "Migration job '$job' starting." );

		if ( ! isset( $jobs[ $job ] ) ) {
			$this->logger->warning( "Migration job '$job' is not defined or not callable." );
			return 0;
		}

		$callback = $jobs[ $job ]['callback'];
		$chunked  = $jobs[ $job ]['chunked'] ?? true;

		try {
			if ( $chunked ) {
				$limit     = static::DEFAULT_CHUNK_SIZE;
				$processed = (int) \call_user_func( $callback, $limit );

				if ( $processed > 0 ) {
					$this->logger->info( "Migration job '$job' processed $processed items." );
				} else {
					$this->logger->info( "Migration job '$job' completed." );
				}

				return $processed;
			}

			\call_user_func( $callback );
			$this->logger->info( "Migration job '$job' completed." );

			return 0;
		} catch ( \Throwable $e ) {
			$this->logger->error( "Migration job '$job' failed: " . $e->getMessage() );
			return 0;
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
	 * @return array<string, array{callback: callable, chunked?: bool, label?: string}>
	 */
	abstract public function get_jobs(): array;

	/**
	 * Defines a job.
	 *
	 * @param callable    $callback The callback to run.
	 * @param string|null $label The label to display to the front-end.
	 * @param bool        $chunked Whether the job processes in chunks.
	 * @return array{callback: callable, chunked?: bool, label?: string}
	 */
	protected function job( callable $callback, ?string $label = null, bool $chunked = true ): array {
		return compact( 'callback', 'label', 'chunked' );
	}
}
