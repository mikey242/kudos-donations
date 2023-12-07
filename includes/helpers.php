<?php
function dd($data) {
	die(print("<pre>".print_r($data,true)."</pre>"));
}