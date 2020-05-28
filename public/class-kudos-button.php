<?php

namespace Kudos;

class Kudos_Button {

	/**
	 * @var string|null
	 */
	private $redirectUrl;
	/**
	 * @var string|null
	 */
	private $label;
	/**
	 * @var string|null
	 */
	private $text;
	/**
	 * @var string|null
	 */
	private $header;
	/**
	 * @var string|null
	 */
	private $style;
	/**
	 * @var bool
	 */
	private $ready;
	/**
	 * @var Kudos_Twig
	 */
	private $twig;


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
		$this->text = !empty($atts['body']) ? $atts['body'] : get_option('_kudos_form_text');
		$this->header = !empty($atts['header']) ? $atts['header'] : get_option('_kudos_form_header');
		$this->style = get_option('_kudos_button_style');
	}

	/**
	 * @since    1.0.0
	 * @param bool $echo
	 * @return string|void
	 */
	public function get_button($echo=true) {

		if($this->ready) {
			$data = [
				'style' => $this->style,
				'header' => $this->header,
				'text' => $this->text,
				'label' => $this->label,
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

