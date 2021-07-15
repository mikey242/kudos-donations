<?php

namespace Kudos\View\Model;

use Kudos\Helpers\Utils;

abstract class AbstractModel {

	const WRAPPER = 'public/wrapper.html.twig';

	/**
	 * The id of the element.
	 *
	 * @var string
	 */
	protected $id;
	/**
	 * The twig template file to use.
	 *
	 * @var string
	 */
	protected $template;
	/**
	 * AbstractRender constructor.
	 */

	public function __construct() {
		$this->id = Utils::generate_id();
		$this->template     = static::TEMPLATE;
	}

	/**
	 * Return the id of the object.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
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