<?php
/************************************************************************
  			mod_assign.php - Copyright 

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
The original location of this file is C:/Users/bs42cavo/uml-generated-code/mod_assign.php
**************************************************************************/

require_once 'mod.php';

/**
 * class mod_assign
 * 
 */
class mod_assign extends mod
{
    public function getTableWithGrades($courseID, $modInstance) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        require_once($CFG->dirroot . '/mod/assign/externallib.php');
        
        $assignment = mod_assign_external::get_grades(array($modInstance));
        if(!isset($assignment['assignments'][0])) {
               $result['cols'] = array();

            $result['cols'][] = array('label' => 'Vorname', 'type' => 'string');
            $result['cols'][] = array('label' => 'Nachname', 'type' => 'string');
            $result['cols'][] = array('label' => 'Bewertung', 'type' => 'number');
            $result['rows'] = array();
            return $result;
        }
        $assignmentid = $assignment['assignments'][0]['assignmentid'];
        $grades = $assignment['assignments'][0]['grades'];
        
        $coursemodule = get_coursemodule_from_instance('assign', $modInstance);
        $coursemodulecontext = context_module::instance($coursemodule->id);
        $course = get_course($courseID);
        
        $assign = new assign($coursemodulecontext, $coursemodule, $course);
        
        $participants = $assign->list_participants(0, false);
        
        $result['cols'] = array();

        $result['cols'][] = array('label' => 'Vorname', 'type' => 'string');
        $result['cols'][] = array('label' => 'Nachname', 'type' => 'string');
        $result['cols'][] = array('label' => 'Bewertung', 'type' => 'number');
        $result['rows'] = array();
        
        foreach($participants as $userid => $user) {
            $grade =  $assign->get_user_grade($userid, false);
            $grade = (empty($grade)) ? 0 : $grade->grade;
            $result['rows'][] = ['c' => array(['v' => $user->firstname], array('v' => $user->lastname), array('v' => $grade))];
        }
        //echo "<pre>".print_r($result, true)."</pre>";
        return $result;
    }
    
    public function getGradesForColumnChart($courseID, $modInstance) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        require_once($CFG->dirroot . '/mod/assign/externallib.php');
        
        $coursemodule = get_coursemodule_from_instance('assign', $modInstance);
        $coursemodulecontext = context_module::instance($coursemodule->id);
        $course = get_course($courseID);
        $assign = new assign($coursemodulecontext, $coursemodule, $course);
        
        $gradeItem = $assign->get_grade_item();
        $grademin = round($gradeItem->grademin, 0);
        $grademax = round($gradeItem->grademax, 0);
        $multfactor = round($gradeItem->multfactor, 0);
        
        $grades = array();
        $i = $grademin;
        while($i <= $grademax) {
            $grades[(int)$i] = 0;
            $i++;
        }
        // All ungraded participants get a 0
        $grades[0] = $assign->count_participants(0) - $assign->count_grades();
        $gradesRaw = mod_assign_external::get_grades(array($modInstance));
        $result['cols'] = array();
        $result['cols'][] = array('label' => 'Bewertung', 'type' => 'number');
        $result['cols'][] = array('label' => 'Anzahl', 'type' => 'number');
        $result['rows'] = array();
        if(!isset($gradesRaw['assignments'][0])) {
            return $result;
        }
        
        foreach($gradesRaw['assignments'][0]['grades'] as $i => $entry) {
            $grade = (int) round($entry['grade'], 0);
            $grades[$grade] = $grades[$grade] + 1;
        }
        
        foreach($grades as $grade => $number) {
            $result['rows'][] = ['c' => array(['v' => $grade], array('v' => $number))];
        }
        //echo "<pre>".print_r($result, true)."</pre>";
        return $result;
    }
    
    public function getDataForPieChart($courseID, $modInstance) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        
        $coursemodule = get_coursemodule_from_instance('assign', $modInstance);
        $coursemodulecontext = context_module::instance($coursemodule->id);
        $course = get_course($courseID);
        $assign = new assign($coursemodulecontext, $coursemodule, $course);
        
        $gradedParticipants = $assign->count_grades();
        $numberOfParticipants = $assign->count_participants(0);
        $ungradedParticipants = $numberOfParticipants - $gradedParticipants;
        
        $result['cols'] = array();
        $result['cols'][] = array('label' => 'Bewertungen', 'type' => 'string');
        $result['cols'][] = array('label' => 'Anzahl', 'type' => 'number');
        $result['rows'] = array();
        $result['rows'][] = ['c' => array(['v' => 'bewertet'], array('v' => $gradedParticipants))];
        $result['rows'][] = ['c' => array(['v' => 'unbewertet'], array('v' => $ungradedParticipants))];
        //echo "<pre>".print_r($result, true)."</pre>";
        return $result;
    }
} // end of mod_assign
?>
