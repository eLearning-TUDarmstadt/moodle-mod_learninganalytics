<?php

/**
 * Translates the localized ModName to the real Mod-Name. Example: File => resource 
 * 
 * @param string $localizedModName For example 'File'
 * @return string example: resource
 */
function localizedNameToModName($localizedModName, $courseID) {
    GLOBAL $USER, $DB;

    $course = get_course($courseID, false);
    $course_modinfo = new course_modinfo($course, $USER->id);
    $moduleNames = $course_modinfo->get_used_module_names();

    foreach ($moduleNames as $modName => $localizedName) {
        if ($localizedName == $localizedModName) {
            return $modName;
        }
    }

    $sql = "SELECT name FROM {modules}";
    $names = $DB->get_records_sql($sql);

    foreach ($names as $obj) {
        //echo "<pre>".print_r($name, true)."</pre>";
        try {
            $modName = get_string('modulename', $obj->name);
        } catch (exception $e) {
            echo $e;
        }

        if ($modName == $localizedModName) {
            return $obj->name;
        }
    }

    echo "Unknown. Possible Names: <br /><pre>" . print_r($moduleNames, true) . "</pre>";
    die;
}


function UnixTimeStampToGoogleDatetimeString($timestamp) {
    $month = (int)date('m',$timestamp) - 1;
    $string = 'Date(' . date('Y,', $timestamp) . $month . date(', d, H, i, s', $timestamp) . ')';
    return $string;
}

?>