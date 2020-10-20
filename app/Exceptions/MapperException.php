<?php

namespace Kudos\Exceptions;

use Throwable;

class MapperException extends AbstractException {

	protected $raisedAt;
	/**
	 * @var string
	 */
	protected $repository;

	/**
	 * MapperException constructor.
	 *
	 * @param string $message
	 * @param int $code
	 * @param string|null $repository
	 * @param Throwable|null $previous
	 */
	public function __construct( $message = "", $code = 0, $repository = null, Throwable $previous = null ) {

		if (!empty($repository)) {
			$this->repository = (string)$repository;
			$message .= ". Repository: {$this->repository}";
		}

		parent::__construct( $message, $code, $previous );
	}

}