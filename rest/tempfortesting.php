<?php
require_once '../../../config.php';

GLOBAL $USER;

$course = get_course(2361, false);

$coursemodinfo = new course_modinfo($course,$USER->id);

$out = $coursemodinfo->get_used_module_names();

echoData($out);

function echoData($data) {
	echo "<pre>".print_r($data, true)."</pre>";
}


