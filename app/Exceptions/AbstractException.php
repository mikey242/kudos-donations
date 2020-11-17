<?php

namespace Kudos\Exceptions;

use DateTime;
use DateTimeImmutable;
use Exception;
use Throwable;

abstract class AbstractException extends Exception {

	/**
	 * DateTime that exception made
	 *
	 * @var DateTimeImmutable
	 */
	protected $raised_at;

	/**
	 * MapperException constructor.
	 *
	 * @param string $message Exception message.
	 * @param int $code Exception code.
	 * @param Throwable|null $previous The previous throwable used for the exception chaining.
	 */
	public function __construct( $message = '', $code = 0, Throwable $previous = null ) {

		$this->raised_at     = new DateTimeImmutable();
		$formatted_raised_at = $this->raised_at->format( DateTime::ISO8601 );
		$message             = "[{$formatted_raised_at}] " . $message;

		parent::__construct( $message, $code, $previous );
	}

}
