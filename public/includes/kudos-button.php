<?php

use Kudos\Kudos_Public;

function kudos_button($label=null, $customText=null, $echo=true) {

	$label = $label ? $label : 'Doneer nu';
	$redirectUrl = Kudos_Public::get_return_url();
	$customText = $customText ? $customText : "Wat lief dat je ons wilt steunen.<br/> Doneer eenmalig zonder verplichtingen.";

	$out = "<button class='kudos-button' data-redirect='$redirectUrl' data-custom-text='$customText'>";
	$out .= "<span class='kudos-logo'></span><span class='kudos_block_button_block__label'>$label</span>";
	$out .= "</button>";

	if($echo) {
		echo $out;
	}

	return $out;

}
