<?php

namespace Kudos\Exceptions;

use Throwable;

class MapperException extends AbstractException {

	/**
	 * Repository
	 *
	 * @var string
	 */
	protected $repository;

	/**
	 * MapperException constructor.
	 *
	 * @param string $message Exception message.
	 * @param int $code Exception code.
	 * @param string|null $repository Repository.
	 * @param Throwable|null $previous The previous throwable used for the exception chaining.
	 */
	public function __construct( $message = '', $code = 0, $repository = null, Throwable $previous = null ) {

		if ( ! empty( $repository ) ) {
			$this->repository = (string) $repository;
			$message          .= ". Repository: {$this->repository}";
		}

		parent::__construct( $message, $code, $previous );
	}

}
