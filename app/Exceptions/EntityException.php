<?php

namespace Kudos\Exceptions;

use Kudos\Entity\AbstractEntity;
use Throwable;

class EntityException extends AbstractException {

	/**
	 * The entity that raised the exception
	 *
	 * @var string
	 */
	protected $entity;

	/**
	 * Property associated with exception
	 *
	 * @var string
	 */
	protected $property;

	/**
	 * MapperException constructor.
	 *
	 * @param string $message Exception message.
	 * @param int $code Exception code.
	 * @param string|null $property Property.
	 * @param AbstractEntity|null $entity Entity.
	 * @param Throwable|null $previous The previous throwable used for the exception chaining.
	 */
	public function __construct(
		$message = '',
		$code = 0,
		$property = null,
		$entity = null,
		Throwable $previous = null
	) {

		if ( ! empty( $property ) ) {
			$this->property = (string) $property;
			$message        .= ". Property: {$this->property}";
		}

		if ( ! empty( $entity ) ) {
			$this->entity = (string) $entity;
			$message      .= ". Entity: {$this->entity}";
		}

		parent::__construct( $message, $code, $previous );
	}

}
