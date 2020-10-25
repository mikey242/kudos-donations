<?php

namespace Kudos\Exceptions;

use Kudos\Entity\AbstractEntity;
use Throwable;

class EntityException extends AbstractException {

	protected $raisedAt;
	/**
	 * @var string
	 */
	protected $entity;

	/**
	 * @var string
	 */
	protected $property;

	/**
	 * MapperException constructor.
	 *
	 * @param string $message
	 * @param int $code
	 * @param string|null $property
	 * @param AbstractEntity|null $entity
	 * @param Throwable|null $previous
	 */
	public function __construct( $message = "", $code = 0, $property = null, $entity = null, Throwable $previous = null ) {

		if (!empty($property)) {
			$this->property = (string)$property;
			$message .= ". Property: {$this->property}";
		}

		if (!empty($entity)) {
			$this->entity = (string)$entity;
			$message .= ". Entity: {$this->entity}";
		}

		parent::__construct( $message, $code, $previous );
	}

}