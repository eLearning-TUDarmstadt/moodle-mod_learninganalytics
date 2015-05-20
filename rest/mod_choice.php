<?php
/************************************************************************
  			mod_choice.php - Copyright 

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
The original location of this file is C:/Users/bs42cavo/uml-generated-code/mod_choice.php
**************************************************************************/

require_once 'mod.php';

/**
 * class mod_choice
 * 
 */
class mod_choice extends mod
{
    public function getChoices($courseID, $modInstance) {
        global $CFG;
        require_once($CFG->dirroot . '/config.php');
        require_once($CFG->dirroot . '/mod/choice/lib.php');
        $choice = choice_get_choice($modInstance);
        $cm = get_coursemodule_from_instance('choice', $modInstance);
        $groupmode = $cm->groupmode;
        $responseData = choice_get_response_data($choice, $cm, $groupmode);
        
        $result['cols'] = array();
        $result['cols'][] = array('label' => 'Vorname', 'type' => 'string');
        $result['cols'][] = array('label' => 'Nachname', 'type' => 'string');
        $result['cols'][] = array('label' => 'e-Mail-Adresse', 'type' => 'string');
        $result['cols'][] = array('label' => 'Abstimmung', 'type' => 'string');
        $result['rows'] = array();
        
        foreach($responseData as $choiceid => $users) {
            $choiceTranslation = choice_get_option_text($choice, $choiceid);
            foreach($users as $userid => $user) {
                $result['rows'][] = ['c' => array(['v' => $user->firstname], array('v' => $user->lastname), array('v' => $user->email), array('v' => $choiceTranslation))];
            }
        }
        
        //echo "<pre>".print_r($result, true)."</pre>";
        return $result;
    }
    public function getParticipation($courseID, $modInstance) {
        global $CFG;
        require_once($CFG->dirroot . '/config.php');
        require_once($CFG->dirroot . '/mod/choice/lib.php');
        $choice = choice_get_choice($modInstance);
        $cm = get_coursemodule_from_instance('choice', $modInstance);
        $groupmode = $cm->groupmode;
        $responseData = choice_get_response_data($choice, $cm, $groupmode);
        $result = array();
        
        
        $options = array();
        $options[0] = array('answer' => '', 'count' => 0);
        foreach ($choice->option as $id => $answer) {
            $options[(int)$id] = array('answer' => $answer, 'count' => 0);
        }
        foreach ($responseData as $optionid => $users) {
            $options[(int)$optionid]['answer'] = choice_get_option_text($choice, $optionid);
            $options[(int)$optionid]['count'] = count($users);
            
        }
        
        $result['cols'] = array();
        $result['cols'][] = array('label' => 'Antwort', 'type' => 'string');
        $result['cols'][] = array('label' => 'Anzahl', 'type' => 'number');
        $result['rows'] = array();
        
        foreach ($options as $optionid => $value) {
            $result['rows'][] = ['c' => array(['v' => $value['answer'].": ".$value['count']], array('v' => $value['count']))];
        }
        
        
        //echo "<pre>".print_r($result, true)."</pre>";
        return $result;
    }
} // end of mod_choice

?>
