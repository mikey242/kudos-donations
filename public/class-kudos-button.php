<?php

use Kudos\Kudos_Public;

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
	 * @param string|null $label
	 * @param string|null $header
	 * @param string|null $text
	 */
	public function __construct($label=null, $header=null, $text=null) {

		$this->redirectUrl = Kudos_Public::get_return_url();
		$this->label = $label ? $label : get_option('_kudos_button_label');
		$this->text = $text ? $text : get_option('_kudos_form_text');
		$this->header = $header ? $header : get_option('_kudos_form_header');
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
			$out = "<button type='button' class='kudos_btn kudos_button_icon $this->style' data-redirect='$this->redirectUrl' data-custom-header='$this->header' data-custom-text='$this->text'>";
				$out .= "<span class='kudos_logo'></span><span class='kudos_block_button_block__label'>$this->label</span>";
			$out .= "</button>";
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

