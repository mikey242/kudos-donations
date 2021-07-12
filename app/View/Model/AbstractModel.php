<?php

namespace Kudos\View\Model;

use Kudos\Helpers\Utils;

abstract class AbstractModel {

	/**
	 * The id of the element.
	 *
	 * @var string
	 */
	protected $container_id;
	/**
	 * The twig template file to use.
	 *
	 * @var
	 */
	protected $template;

	/**
	 * AbstractRender constructor.
	 */
	public function __construct() {
		$this->container_id = Utils::generate_id();
		$this->template     = static::TEMPLATE;
	}

	/**
	 * Return the container_id of the object.
	 *
	 * @return string
	 */
	public function get_container_id(): string {
		return $this->container_id;
	}

	/**
	 * Returns the current object as an array.
	 *
	 * @return array
	 */
	public function to_array(): array {
		return call_user_func('get_object_vars', $this);
	}
}