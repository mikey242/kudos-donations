<?php
/**
 * Log Rest Routes.
 *
 * @link https://github.com/mikey242/kudos-donations/
 *
 * @copyright 2026 Iseard Media
 */

declare(strict_types=1);

namespace IseardMedia\Kudos\Controller\Rest;

use IseardMedia\Kudos\Enum\FieldType;
use WP_Filesystem_Direct;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

// Ensure WordPress filesystem classes loaded.
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

class Log extends BaseRestController {

	private const LOG_DIR = KUDOS_STORAGE_DIR . 'logs/';

	protected string $rest_base = 'log';
	private WP_Filesystem_Direct $file_system;
	private array $log_files;

	/**
	 * Log route constructor.
	 */
	public function __construct() {
		$this->file_system = new WP_Filesystem_Direct( true );
		$this->log_files   = $this->get_logs( $_ENV['APP_ENV'] ?? null );
	}

	/**
	 * Mail service routes.
	 */
	public function get_routes(): array {

		return [
			'/' => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_log' ],
				'permission_callback' => [ $this, 'can_manage_options' ],
				'args'                => [
					'file'  => [
						'type'              => FieldType::STRING,
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'level' => [
						'type'              => FieldType::STRING,
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			],
		];
	}

	/**
	 * Sends a test email using send_message.
	 *
	 * @param WP_REST_Request $request Request array.
	 */
	public function get_log( WP_REST_Request $request ): WP_REST_Response {
		$file   = $request->get_param( 'file' );
		$level  = $request->get_param( 'level' );
		$result = $this->get_log_content( $file, $level );
		return new WP_REST_Response(
			[
				'log_files'    => $this->log_files,
				'current_file' => $result['current_file'],
				'log_entries'  => $result['log_entries'],
			],
			200
		);
	}

	/**
	 * Gets an array of the log file paths.
	 *
	 * @param ?string $env The environment to fetch logs for. If not specified return all.
	 */
	private function get_logs( ?string $env = null ): array {
		$paths = glob( self::LOG_DIR . '*.log' );
		$files = $paths ? array_map( 'basename', $paths ) : $paths;
		if ( $env ) {
			$files = array_values( array_filter( $files, fn( $file ) => substr( $file, 0, \strlen( $env ) ) === $env ) );
		}

		return $files;
	}

	/**
	 * Resolves which log file to use, falling back to the latest available.
	 *
	 * @param string|null $log_file The requested log file name.
	 */
	private function resolve_log_file( ?string $log_file ): ?string {
		if ( $log_file && $this->file_system->exists( self::LOG_DIR . $log_file ) ) {
			return $log_file;
		}
		$latest = $this->log_files ? $this->log_files[ \count( $this->log_files ) - 1 ] : null;
		return $latest && $this->file_system->exists( self::LOG_DIR . $latest ) ? $latest : null;
	}

	/**
	 * Gets the log file as an array.
	 *
	 * @param string|null $log_file The log file name.
	 * @param string      $level The log level to return.
	 */
	private function get_log_content( ?string $log_file, string $level = 'ALL' ): array {
		$current_file = $this->resolve_log_file( $log_file );

		if ( null === $current_file ) {
			return [
				'current_file' => null,
				'log_entries'  => [],
			];
		}

		$lines  = array_filter( explode( "\n", $this->file_system->get_contents( self::LOG_DIR . $current_file ) ) );
		$levels = 'ALL' !== $level ? explode( '|', $level ) : [];

		$log_array = [];
		foreach ( $lines as $line ) {
			$entry = json_decode( $line, true );
			if ( ! $entry || ! \is_array( $entry ) ) {
				continue;
			}
			$level_name = $entry['level_name'] ?? '';
			if ( $levels && ! \in_array( $level_name, $levels, true ) ) {
				continue;
			}
			// Convert JSON format to the expected format for the view.
			$log_array[] = [
				'datetime' => $entry['datetime'] ?? '',
				'channel'  => $entry['channel'] ?? '',
				'level'    => $level_name,
				'message'  => $entry['message'] ?? '',
				'context'  => $entry['context'] ?? [],
				'extra'    => $entry['extra'] ?? [],
			];
		}

		return [
			'current_file' => $current_file,
			'log_entries'  => array_reverse( $log_array ),
		];
	}
}
