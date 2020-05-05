<?php

namespace Kudos\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Kudos_Logger
{

	private $log;
	private $file;
	const LOG_DIR = KUDOS_DIR . 'logs/';

	/**
	 * Kudos_Logger constructor.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
			$this->file = 'kudos.log';
			$this->log = new Logger('kudos');
			$this->log->pushHandler(new StreamHandler(self::LOG_DIR . $this->file));
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
	 * Write message to log file
	 *
	 * @since    1.0.0
	 * @param string $message
	 * @param string|null $level
	 * @param array $context
	 */
	public function log($message, $level=null, $context=[]) {
		$level = ($level ? $level : 'DEBUG');
		$this->log->log($level, $message, $context);
	}

}