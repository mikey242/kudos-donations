<?php

use Kudos\Kudos_Public;

/**
 * @param null $label
 * @param null $customText
 * @param bool $echo
 *
 * @return string
 */
function kudos_button($label=null, $customHeader=null, $customText=null, $echo=true) {

	if(Kudos_Public::ready()) {
		$label = $label ? $label : 'Doneer nu';
		$redirectUrl = Kudos_Public::get_return_url();
		$customText = $customText ? $customText : "Wat lief dat je ons wilt steunen. Doneer eenmalig zonder verplichtingen.";
		$customHeader = $customHeader ? $customHeader : "Steun ons!";
		$style = carbon_get_theme_option('kudos_button_style');

		$out = "<button class='kudos_btn kudos_button_icon $style' data-redirect='$redirectUrl' data-custom-header='$customHeader' data-custom-text='$customText'>";
		$out .= "<span class='kudos_logo'></span><span class='kudos_block_button_block__label'>$label</span>";
		$out .= "</button>";

	} elseif(is_user_logged_in()) {

		$out = 'Mollie not configured';

	} else {

		$out='';

	}

	if($echo) {

		echo $out;

	}

	return $out;

}
