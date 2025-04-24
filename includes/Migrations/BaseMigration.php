<?php
/**
 * BaseMigration class.
 *
 * @link https://gitlab.iseard.media/michael/kudos-donations/
 *
 * @copyright 2024 Iseard Media
 */

namespace IseardMedia\Kudos\Migrations;

use IseardMedia\Kudos\Helper\WpDb;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class BaseMigration implements MigrationInterface {

	protected const DEFAULT_CHUNK_SIZE = 50;

	protected WpDb $wpdb;
	protected LoggerInterface $logger;

	protected array $progress = [];
	protected string $progress_key;

	/**
	 * Constructor for migrations.
	 *
	 * @param WpDb                 $wpdb The WordPress database wrapper.
	 * @param LoggerInterface|null $logger Logger instance.
	 */
	public function __construct( WpDb $wpdb, ?LoggerInterface $logger = null ) {
		$this->wpdb         = $wpdb;
		$this->logger       = $logger ?? new NullLogger();
		$this->progress_key = '_kudos_migration_progress_' . str_replace( '.', '_', $this->get_version() );
		$this->progress     = get_option( $this->progress_key, [] );
	}

	/**
	 * Gets the version number from the static class name.
	 * e.g. Version400 will return 4.0.0 as the version number.
	 */
	public function get_version(): string {
		$class = str_replace( __NAMESPACE__ . '\\', '', static::class );
		$num   = filter_var( $class, FILTER_SANITIZE_NUMBER_INT );
		return implode( '.', str_split( $num ) );
	}

	/**
	 * Returns true if the migration is complete.
	 */
	public function is_complete(): bool {
		if ( ! empty( $this->progress['done'] ) ) {
			$this->clear_progress();
			return true;
		}
		return false;
	}

	/**
	 * Helper to update progress in DB.
	 */
	protected function update_progress(): void {
		update_option( $this->progress_key, $this->progress );
	}

	/**
	 * Reset and delete progress.
	 */
	protected function clear_progress(): void {
		delete_option( $this->progress_key );
	}

	/**
	 * {@inheritDoc}
	 */
	public function step(): bool {
		foreach ( $this->get_migration_jobs() as $step => $job ) {
			$callback = $job['callback'];
			$chunked  = $job['chunked'] ?? false;
			$args     = $job['args'] ?? [];

			// Automatically inject limit if chunked and no args defined.
			if ( $chunked && empty( $args ) ) {
				$args = [ self::DEFAULT_CHUNK_SIZE ];
			}

			if ( $this->run_step( $step, fn() => $callback( ...$args ), $chunked ) ) {
				return true;
			}
		}

		$this->progress['done'] = true;
		$this->update_progress();
		return true;
	}

	/**
	 * Run a named step and automatically mark it as complete when done.
	 *
	 * @param string   $name     Step name key (e.g., 'donors').
	 * @param callable $callback The function to call.
	 * @param bool     $chunked  If true, marks complete only when callback returns true (fully done).
	 * @return bool Returns true if this step consumed the request.
	 */
	protected function run_step( string $name, callable $callback, bool $chunked = false ): bool {
		$done_key = "{$name}_done";

		if ( ! empty( $this->progress[ $done_key ] ) ) {
			return false;
		}

		$this->log_step( $name, 'Starting step...' );

		$result = $callback();

		$this->log_step( $name, 'Step complete', [ 'offset' => $this->progress[ "{$name}_offset" ] ?? 0 ] );

		if ( $chunked ) {
			if ( true === $result ) {
				$this->progress[ $done_key ] = true;
				$this->update_progress();
			}
			return true; // Always return true for chunked to give it a full request.
		}

		// One-time step: assume done after one call.
		$this->progress[ $done_key ] = true;
		$this->update_progress();
		return true;
	}

	/**
	 * Defines a job.
	 *
	 * @param callable    $callback The callback to run.
	 * @param string|null $label The label to display to the front-end.
	 * @param bool        $chunked Whether the job is chunked or not (run in batches).
	 * @param array       $args Any additional args to pass to the callback.
	 */
	protected function job( callable $callback, ?string $label = null, bool $chunked = false, array $args = [] ): array {
		return compact( 'callback', 'label', 'chunked', 'args' );
	}

	/**
	 * Log a step message.
	 *
	 * @param string $step The step name.
	 * @param string $message The message.
	 * @param ?array $context Optional context.
	 */
	protected function log_step( string $step, string $message, array $context = [] ): void {
		$this->logger->info( \sprintf( '%s: %s', strtoupper( $step ), $message ), $context );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_progress_summary(): array {
		$steps   = [];
		$running = null;

		foreach ( $this->get_migration_jobs() as $step => $job ) {
			$done   = ! empty( $this->progress[ "{$step}_done" ] );
			$offset = $this->progress[ "{$step}_offset" ] ?? 0;

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
			'done'    => ! empty( $this->progress['done'] ),
		];
	}
}
