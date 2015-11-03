<?php

require_once 'mod.php';
/************************************************************************
  			mod_forum.php - Copyright 

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
The original location of this file is C:/Users/bs42cavo/uml-generated-code/mod_forum.php
**************************************************************************/

require_once 'mod.php';

class mod_forum extends mod {

    public function getUnseenForumTopics($courseID, $modInstance) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/forum/lib.php');
        $cm = get_coursemodule_from_instance('forum', $modInstance);
        $subjectids = forum_get_discussions($cm);
        // Get all reads/and writes for every discussion.
        // Check, whether a (editing-)teacher has seen or created the discussion.
        // If so, don't list the forum
        $result = array();
        //teacher and higher
        $roles_teacher = get_archetype_roles('teacher');
        $roles_editingteacher = get_archetype_roles('editingteacher');
        $role = array();
        foreach($roles_teacher as $roleid => $roledata) {
            array_push($role, $roleid);
        }
        foreach($roles_editingteacher as $roleid => $roledata) {
            array_push($role, $roleid);
        }
        
        $context = context_course::instance($courseID);
        $teachers = array();
        foreach($role as $roleid) {
            $users = get_role_users($roleid, $context);
            foreach($users as $user) {
                $teachers[] = $user->id;
            }
        }
        foreach ($subjectids as $id => $subject) {
            $discussion = $subject->discussion;
            $sql = "SELECT 
                            userid
                    FROM
                            {logstore_standard_log}
                    WHERE 
                            courseid=$courseID AND
                            component='mod_forum' AND
                            target='discussion' AND 
                            objectid = $discussion AND
                            (action = 'viewed' OR                      
                            action = 'created')
                    GROUP BY
                            userid";
            $users = $DB->get_records_sql($sql);
            
            $seen = false;
            foreach($users as $user) {
                //echo "<pre>" . print_r((array)$user, true) . "</pre>";
                $seen = in_array($user->userid, $teachers);
            }
            if($seen) {
                unset($subjectids[$id]);
            }
        }
        $result['cols'] = array();

        $result['cols'][] = array('label' => 'Titel', 'type' => 'string');
        $result['cols'][] = array('label' => 'Erstellt von', 'type' => 'string');
        $result['cols'][] = array('label' => 'Zuletzt ge&auml;ndert', 'type' => 'date');
        $result['cols'][] = array('label' => 'Antworten', 'type' => 'number');
        $result['rows'] = array();
        foreach ($subjectids as $id => $subject) {
            //echo "<pre>" . print_r($subject, true) . "</pre>";
            $time = $subject->timemodified * 1000;
            $posts = count(forum_get_all_discussion_posts($subject->discussion, 'created DESC'));
            $result['rows'][] = ['c' => array(['v' => $subject->subject], array('v' => $subject->firstname." ".$subject->lastname), array('v' => 'Date('.$time.')'), array('v' => (int)$posts))];
        }
        //echo "<pre>" . print_r($result, true) . "</pre>";
        return $result;
    }

    public function getTopicsAndViewsForCloud($courseID, $modInstance) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/forum/lib.php');
        $cm = get_coursemodule_from_instance('forum', $modInstance);
        $subjectids = forum_get_discussions($cm);
        
        $result['cols'] = array();

        $result['cols'][] = array('label' => 'Discussion', 'type' => 'string');
        $result['cols'][] = array('label' => 'Views', 'type' => 'number');
        $result['cols'][] = array('label' => 'Link', 'type' => 'string');
        $result['rows'] = array();
        
        $oneWeekAgo = time() - 7 * 24 * 60 * 60;
        //echo date('d.m.Y H:i:s',$oneWeekAgo) . "<br />";
        
        foreach ($subjectids as $id => $subject) {
            $discussion = $subject->discussion;
            $sql = "SELECT 
                            id
                    FROM
                            {logstore_standard_log}
                    WHERE 
                            courseid=$courseID AND
                            component='mod_forum' AND
                            target='discussion' AND 
                            objectid = $discussion AND
                            action = 'viewed' AND
                            timecreated > $oneWeekAgo";
            $views = 10 * count($DB->get_records_sql($sql));
            if($views > 0) {
                $topic = $subject->subject;
                $link = '/mod/forum/discuss.php?d='. $discussion;
                $result['rows'][] = ['c' => array(['v' => $topic], array('v' => $views), array('v' => $link))];
            }
        }
        //echo "<pre>" . print_r($result, true) . "</pre>";
        return $result;
    }
}

// end of mod_forum

