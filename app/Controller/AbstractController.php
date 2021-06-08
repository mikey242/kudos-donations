<?php

namespace Kudos\Controller;

use Kudos\Helpers\Utils;
use Kudos\Service\TwigService;

abstract class AbstractController {

	/**
	 * The id of the element.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * The twig template file to use.
	 *
	 * @var
	 */
	protected $template;

	/**
	 * AbstractRender constructor.
	 *
	 * @param string|null $id string
	 */
	public function __construct( string $id = null ) {

		$this->id = $id ?? Utils::generate_id();

	}

	/**
	 * Return the of the object.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * Returns the markup for the current template and data.
	 *
	 * @return string|null
	 */
	protected function get_markup(): ?string {

		$twig = TwigService::factory();

		return $twig->render( $this->template, $this->to_array() );

	}

	/**
	 * @return string|null
	 */
	public function render(): ?string {

		// Container ID cannot be same as object ID.
		$id ='kudos-donations-' . Utils::generate_id();
		return '<div class="kudos-donations" id="' . $id . '">' . $this->get_markup() . '</div>';
	}

	/**
	 * Returns the current object as an array.
	 *
	 * @return array
	 */
	protected function to_array(): array {
		return get_object_vars( $this );
	}

	/**
	 * Returns the rendered html.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->get_markup();
	}
}