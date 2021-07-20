<?php

namespace Kudos\Service;

use DateTimeZone;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as Monolog;

class LoggerService extends Monolog {

	const LOG_DIR = KUDOS_STORAGE_DIR . 'logs/';
	const LOG_FILENAME = 'kudos.log';
	const LOG_SIZE = 2097152; // 2097152 = 2MB
	const LOG_FILE = self::LOG_DIR . self::LOG_FILENAME;

	/**
	 * Kudos_Logger constructor.
	 */
	public function __construct() {

		parent::__construct(
			'kudos',
			[ new StreamHandler( self::LOG_FILE ) ],
			[],
			new DateTimeZone( wp_timezone_string() )
		);

	}

	/**
	 * Clears the log file
	 *
	 * @return bool|int
	 */
	public static function clear() {

		if ( ! self::is_writeable() ) {
			return false;
		}

		return file_put_contents( self::LOG_FILE, '' );

	}

	/**
	 * Checks if log file is writeable and returns true if it is
	 *
	 * @return bool
	 */
	private static function is_writeable(): bool {

		if ( is_writable( self::LOG_DIR ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Downloads the log file
	 */
	public static function download() {

		$file = self::LOG_FILE;

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=kudos_' . sanitize_title( get_bloginfo( 'name' ) ) . '_' . gmdate( 'Y-m-d' ) . '.log' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $file ) );

		readfile( $file );
		exit;

	}

	/**
	 * Create the log directory
	 */
	public function init() {

		if ( wp_mkdir_p( self::LOG_DIR ) ) {
			$this->info( 'Log directory created successfully', [ 'location' => self::LOG_DIR ] );
		}

	}

	/**
	 * Add checks to parent function
	 *
	 * @param int $level Log level.
	 * @param string $message Message to record.
	 * @param array $context Context array.
	 *
	 * @return bool
	 */
	public function addRecord( int $level, string $message, array $context = [] ): bool {

		// Don't log debug if not enabled.
		if ( self::DEBUG === $level && ! KUDOS_DEBUG ) {
			return false;
		}

		// Check ig log is writeable before proceeding.
		if ( ! $this->is_writeable() ) {
			return false;
		}

		return parent::addRecord( $level, $message, $context );
	}

	/**
	 * Get the contents of the log file and return as array.
	 *
	 * @return array
	 */
	public static function get_as_array(): array {

		if ( file_exists( self::LOG_FILE ) ) {

			$limit = 26;
			$reg = '/^\[(?<date>.*)\]\s(?<env>\w+)\.(?<type>\w+):(?<message>.*)/m';

			$lines = [];
			$fp    = fopen( self::LOG_FILE, "r" );
			while ( ! feof( $fp ) ) {
				$line = fgets( $fp, 4096 );
				preg_match( $reg, $line, $matches );
				array_push( $lines, $matches );
				if ( count( $lines ) > $limit ) {
					array_shift( $lines );
				}
			}
			fclose( $fp );

			$lines = array_filter($lines);
			usort( $lines, [ static::class, 'date_compare' ] );

			return $lines;
		}

		return [];

	}

	/**
	 * Compares dates to sort log.
	 *
	 * @param array $a First array.
	 * @param array $b Second array.
	 *
	 * @return false|int
	 */
	private static function date_compare( array $a, array $b ) {

		$t1 = strtotime( $a['date'] );
		$t2 = strtotime( $b['date'] );

		return $t2 - $t1;

	}

}
