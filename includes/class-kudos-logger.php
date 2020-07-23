<?php

namespace Kudos;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Kudos_Logger extends Logger
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
	public function clearLogFile() {
		if(!$this->isWriteable()) {
			return false;
		}
		return file_put_contents(self::LOG_FILE, '');
	}

	/**
	 * Add checks to parent function
	 *
	 * @param string $message
	 * @param string $level
	 * @param array $context
	 *
	 * @return bool
	 * @since    2.0.0
	 */
	public function addRecord($level, $message, $context=[]) :bool {

		// Don't log debug if not enabled
		if($level === self::DEBUG && !WP_DEBUG) {
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
	 * Compares dates to sort log
	 *
	 * @param $a
	 * @param $b
	 * @since   2.0.0
	 * @return false|int
	 */
	private function date_compare($a, $b) {
		$t1 = strtotime($a['date']);
		$t2 = strtotime($b['date']);
		return $t2 - $t1;
	}

}