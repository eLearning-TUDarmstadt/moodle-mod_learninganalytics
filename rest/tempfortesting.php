<<<<<<< HEAD
<<<<<<< HEAD
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



?>
=======
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



?>
>>>>>>> 754baa94d340072e07eebec60dff7181b06f6f26
=======
<?php

require_once '../../../config.php';
$timestamp = strtotime("11-12-10");
$format = "d.m.Y H:i:s";

$date = date($format, $timestamp);
echoData($date);

function echoData($data) {
	echo "<pre>".print_r($data, true)."</pre>";
}



?>
>>>>>>> ceb8c11f23d59d0c85037521adb5a0dab9d84fc8
