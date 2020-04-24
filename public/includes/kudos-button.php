<?php

	function kudos_button($label=null, $echo=true) {

		$label = $label ? $label : 'Doneer nu';

		$out = "<div class='kudos_button'>";
		$out .= "<a><i class='fas fa-hand-holding-heart'></i><span class='kudos_block_button_block__label'>$label</span></a>";
		$out .= "</div>";

		if($echo) {
			echo $out;
		}

		return $out;

	}
