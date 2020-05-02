<?php

use Kudos\Kudos_Public;

/**
 * @param string|null $customLabel
 * @param string|null $customHeader
 * @param string|null $customText
 * @param bool $echo
 *
 * @return string
 */
function kudos_button($customLabel=null, $customHeader=null, $customText=null, $echo=true) {

	if(Kudos_Public::ready()) {
		$redirectUrl = Kudos_Public::get_return_url();
		$label = $customLabel ? $customLabel : carbon_get_theme_option('kudos_button_label');
		$text = $customText ? $customText : carbon_get_theme_option('kudos_form_text');
		$header = $customHeader ? $customHeader : carbon_get_theme_option('kudos_form_header');
		$style = carbon_get_theme_option('kudos_button_style');

		$out = "<button class='kudos_btn kudos_button_icon $style' data-redirect='$redirectUrl' data-custom-header='$header' data-custom-text='$text'>";
			$out .= "<span class='kudos_logo'></span><span class='kudos_block_button_block__label'>$label</span>";
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
