<?php
/**
 * MigrationCacheTrait.
 *
 * Provides a unified cache and progress tracking system using a single WordPress option per migration.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

namespace IseardMedia\Kudos\Migrations;

/**
 * Trait for handling migration step progress and related data.
 */
trait MigrationCacheTrait {

	protected array $progress = [];

	/**
	 * Initialize the progress cache.
	 */
	protected function load_progress(): void {
		$this->progress = get_option( $this->get_progress_key(), [] );
	}

	/**
	 * Get the unique option key used to store this migration's state.
	 *
	 * @return string The progress option key.
	 */
	protected function get_progress_key(): string {
		return '_kudos_migration_progress_' . str_replace( '.', '_', $this->get_version() );
	}

	/**
	 * Retrieve the full progress and cache data from the database.
	 *
	 * @return array The current migration state array.
	 */
	protected function get_progress(): array {
		return $this->progress;
	}

	/**
	 * Save the given progress array to the database.
	 *
	 * @param array $progress The state to store.
	 */
	protected function set_progress( array $progress ): void {
		$this->progress = $progress;
		update_option( $this->get_progress_key(), $progress );
	}

	/**
	 * Permanently delete the migration's state from the database.
	 */
	protected function clear_progress(): void {
		$this->progress = [];
		delete_option( $this->get_progress_key() );
	}

	/**
	 * Determine if the overall migration has completed.
	 *
	 * @return bool True if marked as complete.
	 */
	public function is_complete(): bool {
		$progress = $this->get_progress();
		if ( ! empty( $progress['done'] ) ) {
			$this->clear_progress();
			return true;
		}
		return false;
	}

	/**
	 * Mark the overall migration as done.
	 */
	protected function mark_migration_done(): void {
		$progress         = $this->get_progress();
		$progress['done'] = true;
		$this->set_progress( $progress );

		// Diagnostic.
		$after = get_option( $this->get_progress_key(), [] );
		$this->logger->info( 'Progress after mark_migration_done: ' . print_r( $after, true ) );
	}

	/**
	 * Check if a specific step is completed.
	 *
	 * @param string $step Step key (e.g., 'donors').
	 * @return bool True if completed.
	 */
	protected function is_step_done( string $step ): bool {
		$progress = $this->get_progress();
		return ! empty( $progress[ $step ]['done'] );
	}

	/**
	 * Mark a specific step as done.
	 *
	 * @param string $step Step key (e.g., 'donors').
	 */
	protected function mark_step_done( string $step ): void {
		$progress                  = $this->get_progress();
		$progress[ $step ]['done'] = true;
		$this->set_progress( $progress );
	}

	/**
	 * Get the current offset for a chunked step.
	 *
	 * @param string $step Step key (e.g., 'donors').
	 * @return int The current offset (default 0).
	 */
	protected function get_step_offset( string $step ): int {
		$progress = $this->get_progress();
		return $progress[ $step ]['offset'] ?? 0;
	}

	/**
	 * Increase the offset for a step after processing a chunk.
	 *
	 * @param string $step  Step key.
	 * @param int    $count Number of rows processed in the chunk.
	 */
	protected function increment_step_offset( string $step, int $count ): void {
		$progress                    = $this->get_progress();
		$progress[ $step ]['offset'] = ( $progress[ $step ]['offset'] ?? 0 ) + $count;
		$this->set_progress( $progress );
	}

	/**
	 * Store a value in the cache for a specific step.
	 *
	 * @param string $step Step key.
	 * @param string $key  Subkey for cache entry (e.g., original ID).
	 * @param mixed  $value Cached value (e.g., post ID).
	 */
	protected function cache_set( string $step, string $key, $value ): void {
		$progress                           = $this->get_progress();
		$progress[ $step ]['cache'][ $key ] = $value;
		$this->set_progress( $progress );
	}

	/**
	 * Retrieve a cached value by key for a step.
	 *
	 * @param string $step Step key.
	 * @param string $key  Cache key.
	 * @return mixed|null The cached value, or null if not found.
	 */
	protected function cache_get( string $step, string $key ) {
		$progress = $this->get_progress();
		return $progress[ $step ]['cache'][ $key ] ?? null;
	}

	/**
	 * Get all cached values for a given step.
	 *
	 * @param string $step Step key.
	 * @return array Associative array of cached values.
	 */
	protected function cache_all( string $step ): array {
		$progress = $this->get_progress();
		return $progress[ $step ]['cache'] ?? [];
	}

	/**
	 * Build a frontend-compatible summary of the migration's progress.
	 *
	 * @return array The structured status of all jobs.
	 */
	public function get_progress_summary(): array {
		$steps    = [];
		$running  = null;
		$progress = $this->get_progress();

		foreach ( $this->get_migration_jobs() as $step => $job ) {
			$done   = ! empty( $progress[ $step ]['done'] );
			$offset = $progress[ $step ]['offset'] ?? 0;

			$steps[ $step ] = [
				'label'   => $job['label'] ?? ucfirst( str_replace( '_', ' ', $step ) ),
				'status'  => $done,
				'offset'  => $offset,
				'chunked' => $job['chunked'] ?? false,
			];

			if ( ! $done && null === $running ) {
				$running = $step;
			}
		}

		return [
			'steps'   => $steps,
			'running' => $running,
			'done'    => ! empty( $progress['done'] ),
		];
	}
}
