<?php

namespace Kudos\Exceptions;

use DateTime;
use DateTimeImmutable;
use Exception;
use Throwable;

abstract class AbstractException extends Exception {

	/**
	 * @var DateTimeImmutable
	 */
	protected $raisedAt;

	/**
	 * MapperException constructor.
	 *
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct( $message = "", $code = 0, Throwable $previous = null ) {

		$this->raisedAt = new DateTimeImmutable();
		$formattedRaisedAt = $this->raisedAt->format(DateTime::ISO8601);
		$message = "[{$formattedRaisedAt}] " . $message;

		parent::__construct( $message, $code, $previous );
	}

}