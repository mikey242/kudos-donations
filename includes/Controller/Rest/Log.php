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
	private ?array $log_files;

	/**
	 * Log route constructor.
	 */
	public function __construct() {
		$this->file_system = new WP_Filesystem_Direct( true );
		$this->log_files   = $this->get_logs();
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
		$file        = $request->get_param( 'file' );
		$level       = $request->get_param( 'level' );
		$log_content = $this->get_log_content( $file, $level );
		return new WP_REST_Response(
			[
				'log_files'   => $this->log_files,
				'log_content' => $log_content,
			],
			200
		);
	}

	/**
	 * Gets an array of the log file paths.
	 */
	public static function get_logs(): ?array {
		$files = glob( self::LOG_DIR . '*.log' );
		return $files ? array_map( 'basename', $files ) : $files;
	}

	/**
	 * Gets the log file as an array.
	 *
	 * @param string|null $log_file The log file name.
	 * @param string      $level The log level to return.
	 */
	private function get_log_content( ?string $log_file, string $level = 'ALL' ): array {
		$resolved = $log_file ? self::LOG_DIR . $log_file : null;
		if ( ! $resolved || ! $this->file_system->exists( $resolved ) ) {
			$latest   = \is_array( $this->log_files ) && $this->log_files ? end( $this->log_files ) : '';
			$resolved = $latest ? self::LOG_DIR . $latest : '';
		}

		if ( ! $resolved || ! $this->file_system->exists( $resolved ) ) {
			return [];
		}

		$lines  = array_filter( explode( "\n", $this->file_system->get_contents( $resolved ) ) );
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

		return array_reverse( $log_array );
	}
}
