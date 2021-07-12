<?php

namespace Kudos\View\Model;

class Button extends AbstractModel {

	const TEMPLATE = 'public/button/kudos.button.html.twig';

	/**
	 * Button alignment.
	 *
	 * @var string
	 */
	protected $alignment;
	/**
	 * Button label.
	 *
	 * @var string
	 */
	protected $button_label;
	/**
	 * Logo to show on left of button label.
	 *
	 * @var string|null
	 */
	protected $logo;
	/**
	 * The id of the target modal.
	 *
	 * @var string
	 */
	protected $target;

	/**
	 * Button constructor.
	 */
	public function __construct() {

		parent::__construct();

//		$this->logo = apply_filters( 'kudos_get_button_logo', $this->get_kudos_logo_markup( 'white' ) );

	}

	public function set_atts( array $atts ) {
		$this->alignment    = $atts['alignment'] ?? '';
		$this->button_label = $atts['button_label'] ?? '';
	}

	public function set_target( string $target ) {
		$this->target = $target;
	}

	/**
	 * Returns alignment (left, center, right).
	 *
	 * @return string
	 */
	public function get_alignment(): string {
		return $this->alignment;
	}

	/**
	 * Returns the button label.
	 *
	 * @return string
	 */
	public function get_button_label(): string {
		return $this->button_label;
	}

	/**
	 * Returns the id of the target element.
	 *
	 * @return string
	 */
	public function get_target(): string {
		return $this->target;
	}
}
