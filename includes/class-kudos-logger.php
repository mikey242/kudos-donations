<?php

namespace Kudos;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Kudos_Logger
{

	private $log;
	const LOG_DIR = KUDOS_DIR . 'logs/';
	const LOG_FILE = self::LOG_DIR . 'kudos.log';

	/**
	 * Kudos_Logger constructor.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
			$this->log = new Logger('kudos');
			$this->log->pushHandler(new StreamHandler(self::LOG_FILE));
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
		} else {
			return false;
		}
	}

	/**
	 * Write message to log file
	 *
	 * @since    1.0.0
	 * @param string $message
	 * @param string|null $level
	 * @param array $context
	 */
	public function log($message, $level=null, $context=[]) {
		$level = ($level ? $level : 'INFO');
		if(!$this->isWriteable()) {
			return;
		}
		$this->log->log($level, $message, $context);
	}

}