<?php

namespace Kudos\Front;

use Kudos\Helpers\Settings;
use Kudos\Service\TwigService;

class KudosButton {

	/**
	 * @var TwigService
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
	 * KudosButton constructor.
	 *
	 * @since    1.0.0
	 * @param array $atts
	 */
	public function __construct($atts) {

		$this->twig = new TwigService();
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
			'color' => Settings::get_setting('theme_color'),
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

