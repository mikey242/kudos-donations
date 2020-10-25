<?php

namespace Kudos\Service;

use DateTimeZone;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as Monolog;

//PHP < 7.2 Define it as 0 so it does nothing
if ( ! defined( 'JSON_INVALID_UTF8_SUBSTITUTE' ) ) {
	define( 'JSON_INVALID_UTF8_SUBSTITUTE', 0 );
}

class LoggerService extends Monolog {

	const LOG_DIR = KUDOS_STORAGE_DIR . 'logs/';
	const LOG_FILE = self::LOG_DIR . 'kudos.log';

	/**
	 * Kudos_Logger constructor.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		parent::__construct(
			'kudos',
			[new StreamHandler( self::LOG_FILE )],
			[],
			new DateTimeZone(wp_timezone_string())
		);

	}

	/**
	 * Create the log directory
	 *
	 * @since    1.0.0
	 */
	public static function init() {

		$logger = new LoggerService();

		if ( wp_mkdir_p( self::LOG_DIR ) ) {
			$logger->info( 'Log directory created successfully' );

			return;
		}

		error_log( 'Unable to create Kudos Donations log directory: ' . self::LOG_DIR );

	}

	/**
	 * Clears the log file
	 *
	 * @return bool|false|int
	 * @since   2.0.0
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
	 * @since   1.0.1
	 */
	private static function is_writeable() {

		if ( is_writable( self::LOG_DIR ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Downloads the log file
	 *
	 * @since   2.0.0
	 */
	public static function download() {

		$file = self::LOG_FILE;

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=kudos_' . sanitize_title( get_bloginfo( 'name' ) ) . '_' . date( 'Y-m-d' ) . '.log' );
		header( "Content-Type: application/octet-stream" );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $file ) );

		readfile( $file );
		exit;

	}

	/**
	 * Add checks to parent function
	 *
	 * @param string $message
	 * @param int $level
	 * @param array $context
	 *
	 * @return bool
	 * @since    2.0.0
	 */
	public function addRecord( int $level, string $message, array $context = [] ): bool {

		// Don't log debug if not enabled
		if ( $level === self::DEBUG && ! KUDOS_DEBUG ) {
			return false;
		}

		// Check ig log is writeable before proceeding
		if ( ! $this->is_writeable() ) {
			return false;
		}

		return parent::addRecord( $level, $message, $context );
	}

	/**
	 * Get the contents of the log file and return as array
	 *
	 * @return array|false
	 * @since   2.0.0
	 */
	public function get_as_array() {

		if ( file_exists( self::LOG_FILE ) ) {
			$reg  = '/^\[(?<date>.*)\]\s(?<env>\w+)\.(?<type>\w+):(?<message>.*)/m';
			$text = file_get_contents( self::LOG_FILE );
			preg_match_all( $reg, $text, $matches, PREG_SET_ORDER, 0 );
			usort( $matches, [ $this, 'date_compare' ] );

			return $matches;
		}

		return false;

	}

	/**
	 * Compares dates to sort log
	 *
	 * @param array $a
	 * @param array $b
	 *
	 * @return false|int
	 * @since   2.0.0
	 */
	private function date_compare( array $a, array $b ) {

		$t1 = strtotime( $a['date'] );
		$t2 = strtotime( $b['date'] );

		return $t2 - $t1;

	}

}