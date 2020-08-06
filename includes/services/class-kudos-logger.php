<?php

namespace Kudos\Service;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as Monolog;

//PHP < 7.2 Define it as 0 so it does nothing
if (!defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
	define('JSON_INVALID_UTF8_SUBSTITUTE', 0);
}

class Logger extends Monolog
{

	const LOG_DIR = KUDOS_DIR . 'logs/';
	const LOG_FILE = self::LOG_DIR . 'kudos.log';

	/**
	 * Kudos_Logger constructor.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
			parent::__construct('kudos');
			$this->pushHandler(new StreamHandler(self::LOG_FILE));
	}

	/**
	 * Create the log directory
	 *
	 * @since    1.0.0
	 */
	public static function init() {
		wp_mkdir_p(self::LOG_DIR);
	}

	/**
	 * Checks if log file is writeable and returns true if it is
	 *
	 * @since   1.0.1
	 * @return bool
	 */
	public static function isWriteable() {
		if(is_writable(self::LOG_DIR)) {
			return true;
		}
		return false;
	}

	/**
	 * Clears the log file
	 *
	 * @return bool|false|int
	 * @since   2.0.0
	 */
	public static function clear() {

		$file = self::LOG_FILE;

		// Check nonce
		$nonce = esc_attr( $_REQUEST['_wpnonce'] );
		if ( ! wp_verify_nonce( $nonce, 'clear-' . basename($file) ) ) {
			$logger = new self;
			$logger->warning('Nonce verification failed', ['method' => __METHOD__,'class' => __CLASS__]);
			die();
		}

		if(!self::isWriteable()) {
			return false;
		}
		return file_put_contents($file, '');
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
	public function addRecord(int $level, string $message, array $context=[]) :bool {

		// Don't log debug if not enabled
		if($level === self::DEBUG && !KUDOS_DEBUG) {
			return false;
		}

		// Check ig log is writeable before proceeding
		if(!$this->isWriteable()) {
			return false;
		}

		return parent::addRecord($level,$message,$context);
	}

	/**
	 * Get the contents of the log file and return as array
	 *
	 * @return array
	 * @since   2.0.0
	 */
	public function getAsArray() {
		$reg = '/^\[(?<date>.*)\]\s(?<env>\w+)\.(?<type>\w+):(?<message>.*)/m';
		$text = file_get_contents($this::LOG_FILE);
		preg_match_all($reg, $text, $matches,PREG_SET_ORDER, 0);
		usort($matches, [$this, 'date_compare']);
		return $matches;
	}

	/**
	 * Downloads the log file
	 *
	 * @since   2.0.0
	 */
	public static function download() {

		$file = self::LOG_FILE;

		// Check nonce
		$nonce = esc_attr( $_REQUEST['_wpnonce'] );
		if ( ! wp_verify_nonce( $nonce, 'download-' . basename($file) ) ) {
			$logger = new self;
			$logger->warning('Nonce verification failed', ['method' => __METHOD__,'class' => __CLASS__]);
			die();
		}

		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename=kudos_' . sanitize_title(get_bloginfo('name')) . '_' . date('Y-m-d') . '.log');
		header("Content-Type: text/plain");
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
	}

	/**
	 * Compares dates to sort log
	 *
	 * @param $a
	 * @param $b
	 * @return false|int
	 * @since   2.0.0
	 */
	private function date_compare($a, $b) {
		$t1 = strtotime($a['date']);
		$t2 = strtotime($b['date']);
		return $t2 - $t1;
	}

}