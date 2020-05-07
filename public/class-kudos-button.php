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
	 * Kudos_Button constructor.
	 *
	 * @since    1.0.0
	 * @param array|null $atts
	 */
	public function __construct($atts=[]) {

		$this->redirectUrl = Kudos_Public::get_return_url();
		$this->label = !empty($atts['label']) ? $atts['label'] : get_option('_kudos_button_label');
		$this->text = !empty($atts['text']) ? $atts['text'] : get_option('_kudos_form_text');
		$this->header = !empty($atts['header']) ? $atts['header'] : get_option('_kudos_form_header');
		$this->style = get_option('_kudos_button_style');
		$this->ready = Kudos_Public::ready();
	}

	/**
	 * @since    1.0.0
	 * @param bool $echo
	 * @return string|void
	 */
	public function get_button($echo=true) {

		if($this->ready) {
			$out = "<button type='button' class='kudos_button kudos_button_donate $this->style' data-redirect='$this->redirectUrl' data-custom-header='$this->header' data-custom-text='$this->text'>";
				$out .= "<span class='kudos_logo'></span><span class='kudos_button_label'>$this->label</span>";
			$out .= "</button>";
			do_action('kudos_button');
		} elseif(is_user_logged_in()) {
			$out = __('Mollie not configured', 'kudos-donations');
		} else {
			$out='';
		}

		if($echo) {
			echo $out;
		}

		return $out;
	}


}

