<?php

namespace Kudos;

use Kudos\Service\Twig;

class Kudos_Button {

	/**
	 * @var bool
	 */
	private $ready;
	/**
	 * @var Twig
	 */
	private $twig;
	/**
	 * @var mixed|string
	 */
	private $alignment;
	/**
	 * @var mixed
	 */
	private $target;
	/**
	 * @var bool|mixed|void
	 */
	private $label;

	/**
	 * Kudos_Button constructor.
	 *
	 * @since    1.0.0
	 * @param array $atts
	 */
	public function __construct($atts) {

		$this->twig = new Twig();
		$this->label = $atts['button_label'];
		$this->alignment = $atts['alignment'];
		$this->target = $atts['modal_id'];
	}

	/**
	 * Get the button markup
	 *
	 * @since    1.0.0
	 * @param bool $echo
	 * @return string|void
	 */
	public function get_button($echo=true) {

		$data = [
			'color' => get_kudos_option('theme_color'),
			'alignment' => $this->alignment,
			'label' => $this->label,
			'target' => $this->target
		];

		$out = $this->twig->render('public/kudos.button.html.twig', $data);

		if($echo) {
			echo $out;
		}

		return $out;
	}


}

