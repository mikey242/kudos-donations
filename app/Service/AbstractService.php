<?php

namespace Kudos\Service;

abstract class AbstractService {

	/**
	 * @var LoggerService
	 */
	protected $logger;

	/**
	 * Service constructor.
	 *
	 * @since      2.0.0
	 */
	public function __construct() {

		$this->logger = new LoggerService();

	}

	/**
	 * The class factory. In most cases this
	 * should be used instead of instantiating the object
	 * directly.
	 *
	 * @return static
	 * @since   2.0.0
	 */
	public static function factory() {

		static $instance = false;

		if(!$instance) {
			$instance = new static;
		}

		return $instance;

	}
}