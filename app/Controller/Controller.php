<?php

namespace Kudos\Controller;

use Kudos\Helpers\Utils;
use Kudos\Service\TwigService;

class Controller {

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
	 * @var TwigService
	 */
	private $twig_service;

	/**
	 * AbstractRender constructor.
	 */
	public function __construct( TwigService $twig_service ) {
		$this->twig_service = $twig_service;
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
	 * Returns the markup for the current template and data.
	 *
	 * @param array|null $atts Optional atts to pass to template.
	 *
	 * @return string|null
	 */
	public function render( array $atts = null ): ?string {
		$twig = $this->twig_service;

		return $twig->render( $this->template, $atts ?? $this->to_array() );
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
		return $this->render();
	}
}