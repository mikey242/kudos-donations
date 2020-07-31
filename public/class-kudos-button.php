<?php

namespace Kudos;

class Kudos_Button {

	/**
	 * @var bool
	 */
	private $ready;
	/**
	 * @var Kudos_Twig
	 */
	private $twig;
	/**
	 * @var mixed|string
	 */
	private $alignment;
	/**
	 * @var bool|mixed|void
	 */
	private $color;
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
	 * @param array|null $atts
	 */
	public function __construct($atts=[]) {

		$this->ready = Kudos_Public::ready();
		$this->twig = new Kudos_Twig();
		$this->label = !empty($atts['button']) ? $atts['button'] : get_option('_kudos_button_label');
		$this->color = !empty($atts['color']) ? $atts['color'] : get_option('_kudos_button_color');
		$this->alignment = !empty($atts['alignment']) ? $atts['alignment'] : 'left';
		$this->target = $atts['target'];
	}

	/**
	 * @since    1.0.0
	 * @param bool $echo
	 * @return string|void
	 */
	public function get_button($echo=true) {

		if($this->ready) {
			$data = [
				'color' => $this->color,
				'alignment' => $this->alignment,
				'label' => $this->label,
				'target' => $this->target
			];
			$out = $this->twig->render('public/kudos.button.html.twig', $data);
		} elseif(is_user_logged_in()) {
			$out = "<a href=". esc_url( admin_url('?page=kudos-settings')) .">" . __('Mollie not connected', 'kudos-donations') . "</a>";
		} else {
			$out='';
		}

		if($echo) {
			echo $out;
		}

		return $out;
	}


}

