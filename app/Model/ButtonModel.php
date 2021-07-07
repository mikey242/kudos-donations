<?php

namespace Kudos\Model;

use Kudos\Helpers\Utils;

class ButtonModel extends AbstractModel {

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
	 *
	 * @param array $atts Array of above attributes.
	 * @param string $target Target modal id.
	 *
	 * @since    1.0.0
	 */
	public function __construct( array $atts, string $target ) {

		parent::__construct();

		$this->template = self::TEMPLATE;
		$this->logo     = apply_filters( 'kudos_get_button_logo', Utils::get_kudos_logo_markup( 'white' ) );
		$this->target = $target;

		// Assign button atts to properties.
		$this->alignment    = $atts['alignment'] ?? '';
		$this->button_label = $atts['button_label'] ?? '';

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
