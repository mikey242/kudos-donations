<?php

namespace Kudos\View\Model;

class Button extends AbstractModel {

	const TEMPLATE = 'public/button/kudos.button.html.twig';

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
	}

	/**
	 * Set the attributes.
	 *
	 * @param array $atts
	 */
	public function set_atts( array $atts ) {
		$this->button_label = $atts['button_label'] ?? '';
	}

	/**
	 * Set the target modal id.
	 *
	 * @param string $target
	 */
	public function set_target( string $target ) {
		$this->target = $target;
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
