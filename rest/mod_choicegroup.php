<?php
/* * **********************************************************************
  mod_choicegroup.php - Copyright

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
  The original location of this file is C:/Users/bs42cavo/uml-generated-code/mod_choicegroup.php
 * ************************************************************************ */
/************************************************************************
  			mod_choicegroup.php - Copyright 

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
The original location of this file is C:/Users/bs42cavo/uml-generated-code/mod_choicegroup.php
**************************************************************************/

require_once 'mod.php';


class mod_choicegroup extends mod {

    public function getModStatsInCourse($courseID) {
        global $CFG;

        require_once($CFG->dirroot . '/group/lib.php');
        require_once($CFG->libdir . '/completionlib.php');
        require_once $CFG->dirroot . '/mod/choicegroup/lib.php';

        $cms = get_coursemodules_in_course('choicegroup', $courseID);
        $result['cols'] = array();

        $result['cols'][] = array('label' => 'Gruppenabstimmung', 'type' => 'string');
        $result['cols'][] = array('label' => 'Studierende', 'type' => 'number');
        $result['cols'][] = array('label' => 'nicht abgestimmt', 'type' => 'number');
        $result['cols'][] = array('label' => 'abgestimmt', 'type' => 'number');

        $result['rows'] = array();
        foreach ($cms as $id => $cm) {
            $choicegroup = choicegroup_get_choicegroup($cm->instance);
            //echo "<pre>".print_r($choicegroup, true)."</pre>";
            global $context;
            $context = context_module::instance($cm->id);
            $allresponses = choicegroup_get_response_data($choicegroup, $cm);

            $roles = get_archetype_roles('student');
            $usersInCourse = 0;
            foreach ($roles as $roleid => $role) {
                $users = get_users_from_role_on_context($role, context_course::instance($courseID));
                $usersInCourse += count($users);
            }
            $amountOfUsersWithoutVoting = 0;
            if (isset($allresponses[0])) {
                $amountOfUsersWithoutVoting = count($allresponses[0]);
            }
            $result['rows'][] = ['c' => array(['v' => $choicegroup->name], array('v' => $usersInCourse), array('v' => $amountOfUsersWithoutVoting), array('v' => $usersInCourse - $amountOfUsersWithoutVoting))];
        }
        echo "<pre>" . print_r($result, true) . "</pre>";
        //return $result;
    }

    public function getModStatsForInstance($courseID, $instance) {
        global $CFG;
        require_once($CFG->dirroot . '/group/lib.php');
        require_once($CFG->libdir . '/completionlib.php');
        require_once $CFG->dirroot . '/mod/choicegroup/lib.php';

        $result = choicegroup_get_choicegroup($instance);
        echo "<pre>" . print_r($result, true) . "</pre>";
        //return $result;
    }

    public function getSumOfUsersAndGroupLimits($courseID, $instance) {
        global $CFG;
        require_once($CFG->dirroot . '/enrol/locallib.php');
        require_once($CFG->dirroot . '/group/lib.php');
        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->libdir . '/grouplib.php');
        require_once $CFG->dirroot . '/mod/choicegroup/lib.php';
        $roles = get_archetype_roles('student');
        $role = array();
        foreach($roles as $roleid => $roledata) {
            $role[] = $roleid;
        }
        $context = context_course::instance($courseID);
        $users = get_role_users($role, $context);
        $result = array();
        $result['usersTotal'] = count($users);

        $choicegroup = choicegroup_get_choicegroup($instance);
        if ($choicegroup->limitanswers == 0) {
            $result['sumOfGroupLimitations'] = -1;
        } else {
            $sum = 0;
            foreach ($choicegroup->maxanswers as $groupid => $limit) {
                $sum += $limit;
            }
            $result['sumOfGroupLimitations'] = $sum;
        }
        //echo "<pre>".print_r($choicegroup, true)."</pre>";
        return $result;
    }

    public function IsThereAGroupOverloaded($courseID, $instance) {
        global $CFG;
        require_once($CFG->dirroot . '/group/lib.php');
        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->libdir . '/grouplib.php');
        require_once $CFG->dirroot . '/mod/choicegroup/lib.php';

        $choicegroup = choicegroup_get_choicegroup($instance);

        foreach ($choicegroup->option as $choicegroupid => $groupid) {
            $limit = $choicegroup->maxanswers[$choicegroupid];
            $usersInGroup = count(groups_get_members($groupid));

            if ($usersInGroup > $limit) {
                return true;
            }
        }
        return false;
    }

    public function getPieChartData($courseID, $instance) {
        global $CFG;
        require_once($CFG->dirroot . '/enrol/locallib.php');
        $page = new moodle_page();
        $enrolment_manager = new course_enrolment_manager($page, get_course($courseID));
        
        $roles = get_archetype_roles('student');
        $role = array();
        foreach($roles as $roleid => $roledata) {
            $role[] = $roleid;
        }
        $context = context_course::instance($courseID);
        $users = get_role_users($role, $context);

        $users_total = count($users);
        $usersInAGroup = 0;
        $usersWithoutGroup = 0;

        foreach ($users as $userid => $data) {
            $user_groups = $enrolment_manager->get_user_groups($userid);
            if (empty($user_groups)) {
                $usersWithoutGroup++;
            } else {
                $usersInAGroup++;
            }
        }
        $result['cols'] = array();

        $result['cols'][] = array('label' => 'Gruppe', 'type' => 'string');
        $result['cols'][] = array('label' => 'Anzahl', 'type' => 'number');
        $result['rows'][] = ['c' => array(['v' => 'Teilnehmer in Gruppe: ' . $usersInAGroup], array('v' => $usersInAGroup))];
        $result['rows'][] = ['c' => array(['v' => 'Teilnehmer ohne Gruppe: ' . $usersWithoutGroup], array('v' => $usersWithoutGroup))];


        //echo "<pre>".print_r($result, true)."</pre>";
        return $result;
    }

    public function getTableWithAllGroupsInChoice($courseID, $instance) {
        global $CFG;
        require_once($CFG->dirroot . '/group/lib.php');
        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->libdir . '/grouplib.php');
        require_once $CFG->dirroot . '/mod/choicegroup/lib.php';

        $result['cols'] = array();

        $result['cols'][] = array('label' => 'Gruppe', 'type' => 'string');
        $result['cols'][] = array('label' => 'Teilnehmer in Gruppe', 'type' => 'number');
        $result['cols'][] = array('label' => 'Gruppenlimit', 'type' => 'number');
        $result['cols'][] = array('label' => 'Prozent', 'type' => 'number');
        $result['cols'][] = array('label' => 'Voll', 'type' => 'boolean');
        $result['rows'] = array();

        $choicegroup = choicegroup_get_choicegroup($instance);
        //echo "<pre>".print_r($choicegroup, true)."</pre>";
        $groupsInCourse = groups_get_all_groups($courseID);

        foreach ($choicegroup->option as $choicegroupid => $groupid) {
            $name = $groupsInCourse[$groupid]->name;
            $usersInGroup = count(groups_get_members($groupid));
            //echo "<pre>".print_r($usersInGroup, true)."</pre>";
            //$usersInGroup = 1;
            $groupLimit = $choicegroup->maxanswers[$choicegroupid];
            $percentage = ($groupLimit != 0) ? round(100 * $usersInGroup / $groupLimit, 2) : 0;
            $full = ($usersInGroup >= $groupLimit) ? true : false;
            $result['rows'][] = ['c' => array(['v' => $name], array('v' => $usersInGroup), array('v' => $groupLimit), array('v' => $percentage, 'f' => $percentage . "%"), array('v' => $full))];
        }
        //echo "<pre>".print_r($result, true)."</pre>";
        return $result;
    }
}