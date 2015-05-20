<?php

/* * **********************************************************************
  mod_feedback.php - Copyright

  Here you can write a license for your code, some comments or any other
  information you want to have in your generated code. To to this simply
  configure the "headings" directory in uml to point to a directory
  where you have your heading files.

  or you can just replace the contents of this file with your own.
  If you want to do this, this file is located at

  C:/Users/bs42cavo/kde/share/apps/umbrello/headings/heading.php

  -->Code Generators searches for heading files based on the file extension
  i.e. it will look for a file name ending in ".h" to include in C++ header
  files, and for a file name ending in ".java" to include in all generated
  java code.
  If you name the file "heading.<extension>", Code Generator will always
  choose this file even if there are other files with the same extension in the
  directory. If you name the file something else, it must be the only one with that
  extension in the directory to guarantee that Code Generator will choose it.

  you can use variables in your heading files which are replaced at generation
  time. possible variables are : author, date, time, filename and filepath.
  just write %variable_name%

  This file was generated on Di Sep 23 2014 at 14:08:46
  The original location of this file is C:/Users/bs42cavo/uml-generated-code/mod_feedback.php
 * ************************************************************************ */

require_once 'mod.php';
require_once 'lib.php';

/**
 * class mod_feedback
 * 
 */
class mod_feedback extends mod {

    public function getParticipation($courseID, $modInstance) {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/config.php');
        require_once($CFG->dirroot . '/mod/feedback/lib.php');

        $course = get_course($courseID);
        $course_module = get_coursemodule_from_instance('feedback', $modInstance);
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($course_module->id);

        $complete_users = feedback_count_complete_users($cm);
        $incomplete_users = feedback_count_incomplete_users($cm);

        $result['cols'] = array();
        $result['cols'][] = array('label' => 'Beteiligung', 'type' => 'string');
        $result['cols'][] = array('label' => 'Anzahl', 'type' => 'number');
        $result['rows'] = array();

        $result['rows'][] = ['c' => array(['v' => 'Feedback abgegeben'], array('v' => $complete_users))];
        $result['rows'][] = ['c' => array(['v' => 'Kein Feedback abgegeben'], array('v' => $incomplete_users))];

        //echo "<pre>".print_r($result, true)."</pre>";
        return $result;
    }


    public function getUsersWithCompletedFeedback($courseID, $modInstance) {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/config.php');
        require_once($CFG->dirroot . '/mod/feedback/lib.php');

        $course = get_course($courseID);
        $course_module = get_coursemodule_from_instance('feedback', $modInstance);
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($course_module->id);

        $users = feedback_get_complete_users($cm,false,'',null,'',0,100000000000000);
        $result['cols'] = array();
        $result['cols'][] = array('label' => 'Vorname', 'type' => 'string');
        $result['cols'][] = array('label' => 'Nachname', 'type' => 'string');
        $result['cols'][] = array('label' => 'eMail', 'type' => 'string');
        $result['cols'][] = array('label' => 'Feedback am', 'type' => 'datetime');
        $result['rows'] = array();
        
        foreach($users as $userid => $values) {
            $result['rows'][] = ['c' => array(['v' => $values->firstname], array('v' => $values->lastname), array('v' => $values->email), array('v' => UnixTimeStampToGoogleDatetimeString($values->completed_timemodified)))];
        }
        
        //echo "<pre>".print_r($result, true)."</pre>";
        return $result;
    }
    
    public function getUsersWithoutCompletedFeedback($courseID, $modInstance) {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/config.php');
        require_once($CFG->dirroot . '/mod/feedback/lib.php');
        require_once($CFG->dirroot . '/user/lib.php');
        $course = get_course($courseID);
        $course_module = get_coursemodule_from_instance('feedback', $modInstance);
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($course_module->id);
        
        //$users = feedback_get_incomplete_users($cm,false,'',null,'',0,100000000000000);
        $users = feedback_get_incomplete_users($cm);
        $users = user_get_users_by_id($users);
        //print_r($users);
        $result['cols'] = array();
        $result['cols'][] = array('label' => 'Vorname', 'type' => 'string');
        $result['cols'][] = array('label' => 'Nachname', 'type' => 'string');
        $result['cols'][] = array('label' => 'eMail', 'type' => 'string');
        $result['rows'] = array();
        
        foreach($users as $userid => $values) {
            $result['rows'][] = ['c' => array(['v' => $values->firstname], array('v' => $values->lastname), array('v' => $values->email))];
        }
        
        //echo "<pre>".print_r($result, true)."</pre>";
        return $result;
    }
}
// end of mod_feedback
?>
