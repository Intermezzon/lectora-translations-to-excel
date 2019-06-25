<?php

function splitHTML($in) {
	$split = preg_split('/(<[^>]*>\s*)/i', $in, -1, PREG_SPLIT_DELIM_CAPTURE);
	$isHTML = null;
	$out = [];
	foreach ($split as $s) {
		if (substr($s, 0, 1) == '<') {
			if ($isHTML === true) {
				$out[count($out)-1] .= $s;
			} else {
				$isHTML = true;
				$out[] = $s;
			}
		} else {
			if (strlen($s)) {
				$out[] = $s;
				$isHTML = false;
			}
		}
	}
	return $out;
}
