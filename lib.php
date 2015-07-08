<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of functions and constants for module learninganalytics
 *
 * @package mod_learninganalytics
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/** learninganalytics_MAX_NAME_LENGTH = 50 */
//define("learninganalytics_MAX_NAME_LENGTH", 50);

/**
 * @uses learninganalytics_MAX_NAME_LENGTH
 * @param object $learninganalytics
 * @return string
 */
function get_learninganalytics_name($learninganalytics) {
    /*
    $name = strip_tags(format_string($learninganalytics->intro,true));
    if (core_text::strlen($name) > learninganalytics_MAX_NAME_LENGTH) {
        $name = core_text::substr($name, 0, learninganalytics_MAX_NAME_LENGTH)."...";
    }

    if (empty($name)) {
        // arbitrary name
        $name = get_string('modulename','learninganalytics');
    }

    return $name;
     
     */
    return $learninganalytics->intro;
}
/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $learninganalytics
 * @return bool|int
 */
function learninganalytics_add_instance($learninganalytics) {
    global $DB, $CFG, $COURSE;
	$data = new stdClass();
	
	$data->course = $learninganalytics->course;
	$data->name = "LearningAnalytics";
	
	//$filename = $CFG->dirroot . "/mod/learninganalytics/html/JSLoader.html";
	//$content =  file_get_contents($filename);
        require_once('rest/test.php');
        $html = activityCloud::getCloudLoader();
        $data->intro = str_replace('COURSEID', $COURSE->id, $html);
	//$data->intro = str_replace('COURSE', $COURSE->id, $content);
	$data->introformat = 0;
	$data->timemodified = time();
	//echo "<pre>".print_r($CFG, true)."</pre>";
    return $DB->insert_record("learninganalytics", $data);
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $learninganalytics
 * @return bool
 */
function learninganalytics_update_instance($learninganalytics) {
    global $DB;

    $learninganalytics->name = get_learninganalytics_name($learninganalytics);
    $learninganalytics->timemodified = time();
    $learninganalytics->id = $learninganalytics->instance;

    return $DB->update_record("learninganalytics", $learninganalytics);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function learninganalytics_delete_instance($id) {
    global $DB;

    if (! $learninganalytics = $DB->get_record("learninganalytics", array("id"=>$id))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("learninganalytics", array("id"=>$learninganalytics->id))) {
        $result = false;
    }

    return $result;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 *
 * @global object
 * @param object $coursemodule
 * @return cached_cm_info|null
 */
function learninganalytics_get_coursemodule_info($coursemodule) {
    global $DB, $CFG, $COURSE;

    if ($learninganalytics = $DB->get_record('learninganalytics', array('id'=>$coursemodule->instance), 'id, name, intro, introformat')) {
        if (empty($learninganalytics->name)) {
            // learninganalytics name missing, fix it
            $learninganalytics->name = "learninganalytics{$learninganalytics->id}";
            $DB->set_field('learninganalytics', 'name', $learninganalytics->name, array('id'=>$learninganalytics->id));
        }
        $info = new cached_cm_info();
        // no filtering hre because this info is cached and filtered later
        //$info->content = format_module_intro('learninganalytics', $learninganalytics, $coursemodule->id, false);
        
        //require_once $CFG->dirroot . '/mod/learninganalytics/rest/test.php';
        //$cloud = new \activityCloud($COURSE->id);
        //$cloud = new activityCloud(64);
        //$cloud->printArray($cloud);
        //$cloud->printArray($cloud->getCloud());
        /*
        $html = "<div id='learninganalytics_div'></div><script src='".$CFG->wwwroot."/mod/learninganalytics/js/jquery-2.1.1.min.js'></script><script>"
                . '$.get( "'.$CFG->wwwroot.'/mod/learninganalytics/rest/test.php?courseid='.$COURSE->id.'", function( data ) {
                            $( "#learninganalytics_div" ).html( data );
                          });
                    '
                . "</script>";
        */
        $info->content = $learninganalytics->intro;
        $info->name  = $learninganalytics->name;
        return $info;
    } else {
        return null;
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function learninganalytics_reset_userdata($data) {
    return array();
}

/**
 * Returns all other caps used in module
 *
 * @return array
 */
function learninganalytics_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * @uses FEATURE_IDNUMBER
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool|null True if module supports feature, false if not, null if doesn't know
 */
function learninganalytics_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER:                return false;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_NO_VIEW_LINK:            return true;

        default: return null;
    }
}

/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 */
function learninganalytics_dndupload_register() {
    $strdnd = get_string('dnduploadlearninganalytics', 'mod_learninganalytics');
    if (get_config('learninganalytics', 'dndmedia')) {
        $mediaextensions = file_get_typegroup('extension', 'web_image');
        $files = array();
        foreach ($mediaextensions as $extn) {
            $extn = trim($extn, '.');
            $files[] = array('extension' => $extn, 'message' => $strdnd);
        }
        $ret = array('files' => $files);
    } else {
        $ret = array();
    }

    $strdndtext = get_string('dnduploadlearninganalyticstext', 'mod_learninganalytics');
    return array_merge($ret, array('types' => array(
        array('identifier' => 'text/html', 'message' => $strdndtext, 'noname' => true),
        array('identifier' => 'text', 'message' => $strdndtext, 'noname' => true)
    )));
}

/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function learninganalytics_dndupload_handle($uploadinfo) {
    global $USER;

    // Gather the required info.
    $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '';
    $data->introformat = FORMAT_HTML;
    $data->coursemodule = $uploadinfo->coursemodule;

    // Extract the first (and only) file from the file area and add it to the learninganalytics as an img tag.
    if (!empty($uploadinfo->draftitemid)) {
        $fs = get_file_storage();
        $draftcontext = context_user::instance($USER->id);
        $context = context_module::instance($uploadinfo->coursemodule);
        $files = $fs->get_area_files($draftcontext->id, 'user', 'draft', $uploadinfo->draftitemid, '', false);
        if ($file = reset($files)) {
            if (file_mimetype_in_typegroup($file->get_mimetype(), 'web_image')) {
                // It is an image - resize it, if too big, then insert the img tag.
                $config = get_config('learninganalytics');
                $data->intro = learninganalytics_generate_resized_image($file, $config->dndresizewidth, $config->dndresizeheight);
            } else {
                // We aren't supposed to be supporting non-image types here, but fallback to adding a link, just in case.
                $url = moodle_url::make_draftfile_url($file->get_itemid(), $file->get_filepath(), $file->get_filename());
                $data->intro = html_writer::link($url, $file->get_filename());
            }
            $data->intro = file_save_draft_area_files($uploadinfo->draftitemid, $context->id, 'mod_learninganalytics', 'intro', 0,
                                                      null, $data->intro);
        }
    } else if (!empty($uploadinfo->content)) {
        $data->intro = $uploadinfo->content;
        if ($uploadinfo->type != 'text/html') {
            $data->introformat = FORMAT_PLAIN;
        }
    }

    return learninganalytics_add_instance($data, null);
}

/**
 * Resize the image, if required, then generate an img tag and, if required, a link to the full-size image
 * @param stored_file $file the image file to process
 * @param int $maxwidth the maximum width allowed for the image
 * @param int $maxheight the maximum height allowed for the image
 * @return string HTML fragment to add to the learninganalytics
 */
function learninganalytics_generate_resized_image(stored_file $file, $maxwidth, $maxheight) {
    global $CFG;

    $fullurl = moodle_url::make_draftfile_url($file->get_itemid(), $file->get_filepath(), $file->get_filename());
    $link = null;
    $attrib = array('alt' => $file->get_filename(), 'src' => $fullurl);

    if ($imginfo = $file->get_imageinfo()) {
        // Work out the new width / height, bounded by maxwidth / maxheight
        $width = $imginfo['width'];
        $height = $imginfo['height'];
        if (!empty($maxwidth) && $width > $maxwidth) {
            $height *= (float)$maxwidth / $width;
            $width = $maxwidth;
        }
        if (!empty($maxheight) && $height > $maxheight) {
            $width *= (float)$maxheight / $height;
            $height = $maxheight;
        }

        $attrib['width'] = $width;
        $attrib['height'] = $height;

        // If the size has changed and the image is of a suitable mime type, generate a smaller version
        if ($width != $imginfo['width']) {
            $mimetype = $file->get_mimetype();
            if ($mimetype === 'image/gif' or $mimetype === 'image/jpeg' or $mimetype === 'image/png') {
                require_once($CFG->libdir.'/gdlib.php');
                $tmproot = make_temp_directory('mod_learninganalytics');
                $tmpfilepath = $tmproot.'/'.$file->get_contenthash();
                $file->copy_content_to($tmpfilepath);
                $data = generate_image_thumbnail($tmpfilepath, $width, $height);
                unlink($tmpfilepath);

                if (!empty($data)) {
                    $fs = get_file_storage();
                    $record = array(
                        'contextid' => $file->get_contextid(),
                        'component' => $file->get_component(),
                        'filearea'  => $file->get_filearea(),
                        'itemid'    => $file->get_itemid(),
                        'filepath'  => '/',
                        'filename'  => 's_'.$file->get_filename(),
                    );
                    $smallfile = $fs->create_file_from_string($record, $data);

                    // Replace the image 'src' with the resized file and link to the original
                    $attrib['src'] = moodle_url::make_draftfile_url($smallfile->get_itemid(), $smallfile->get_filepath(),
                                                                    $smallfile->get_filename());
                    $link = $fullurl;
                }
            }
        }

    } else {
        // Assume this is an image type that get_imageinfo cannot handle (e.g. SVG)
        $attrib['width'] = $maxwidth;
    }

    $img = html_writer::empty_tag('img', $attrib);
    if ($link) {
        return html_writer::link($link, $img);
    } else {
        return $img;
    }
}
