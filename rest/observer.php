<?php

/* * **********************************************************************
  observer.php - Copyright

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

  This file was generated on Di Sep 23 2014 at 14:08:47
  The original location of this file is C:/Users/bs42cavo/uml-generated-code/observer.php
 * ************************************************************************ */

require_once 'iface_mod.php';
require_once '../../../config.php';

/**
 * class observer
 *
 */
class observer {

    public $course;
    public $modulesInCourse = array();
    private $dateformat = "d.m."; //Y H:i:s";

    /**
     * __construct
     *
     * @param int course
     * @return void
     * @access public
     */

    public function __construct($course) {
        $this->startDate = strtotime(date('d-m-Y', time() - 6 * 24 * 60 * 60));
        $this->endDate = strtotime(date('d-m-Y')) + 24 * 60 * 60 - 1;
        GLOBAL $DB;
        $this->course = $course;
        $SQL = "SELECT component FROM {logstore_standard_log} WHERE action = 'viewed' AND courseid=" . $course . " AND component LIKE 'mod%' AND timecreated >= " . $this->startDate . " AND timecreated <= " . $this->endDate . " GROUP BY component";
        $modules = $DB->get_records_sql($SQL);
        //echo "<pre>".print_r($SQL, true)."</pre>";
        foreach ($modules as $module => $value) {
            require_once $module . '.php';
            $this->modulesInCourse[$module] = new $module();
        }
        //echo "<pre>".print_r($this->modulesInCourse, true)."</pre>";
    }

    public function collectUniqueViews() {
        GLOBAL $CFG, $DB, $OUTPUT;
        require_once $CFG->dirroot . '/course/externallib.php';

        $course = get_course($this->course);
        $sections_with_modules = core_course_external::get_course_contents($course->id);
        //echo "<pre>" . print_r($sections_with_modules, true) . "</pre>";

        $result = array();
        $result[] = array('Label', 'Value', 'Link', 'Anything', 'modid', 'instance', 'name');
        //$result[] = array($course->fullname, null, 0, 0, null, null, null);
        foreach ($sections_with_modules as $section => $content) {
            if (!empty($content['modules'])) {
                $counter = 0;
                foreach ($content['modules'] as $index => $module) {
                    //echo "<pre>".print_r($module, true)."</pre>";
                    $sql = "SELECT
                                        id,
					userid
				FROM
					{logstore_standard_log}
				WHERE
					action = 'viewed' AND
					component = 'mod_" . $module['modname'] . "' AND
					courseid = " . $course->id . " AND
					timecreated >= " . $this->startDate . " AND
					timecreated <= " . $this->endDate . " AND
                                        contextinstanceid = " . $module['id'] . "
				
				";
                                // Add GROUP BY userid to get unique views
                    $res = $DB->get_records_sql($sql);
                    $views = count($res);
                    
                    $moduleID = $DB->get_record('modules', array('name' => $module['modname']), 'id');
                    $moduleID = $moduleID->id;
                    //echo "<pre>".print_r($moduleID, true)."</pre>";
                    if ($views > 0) {
                        //Hier koennte man ggf das icon der jew. Aktivitaet uebertragen
                        ob_start();
                        echo $OUTPUT->pix_url('icon', 'mod_'.$module['modname']);
                        $iconLink = ob_get_clean(); 
                        //$icon = '<img src="'.$iconLink.'"></img>';
                        $icon = '';
                        $name = str_replace(' ', '&nbsp;', $module['name']);
                        $name = $icon.$name; //." - ".$views. " Views";
                        //$name = $module['name'];
                        $result[] = array($name,(int) $views,$iconLink , $moduleID, $module['id'], $module['instance'], $module['modname']);
                        $counter += $views;
                    }
                } 
                /*
                if ($counter > 0) {
                    $result[] = array($content['name'], $course->fullname, $counter, 0, null, null, null);
                }
                 */
            }
        }
        //echo "<pre>" . print_r($result, true) . "</pre>";
        return $result;
        /*
          $result = array();
          $data = array();
          $header = array();
          $dateCounter = $this->startDate;

          $header[] = array('label' => 'Name', 'type' => 'string');
          while ($dateCounter <= $this->endDate) {
          $printableDate = date($this->dateformat, $dateCounter);
          $header[] = array('label' => $printableDate, 'type' => 'string');
          foreach ($this->modulesInCourse as $index => $module) {
          $count = $module->getUniqueViews($this->course, $dateCounter, $dateCounter + 24 * 60 * 60 - 1);
          $data[$module->locatedPluginName][] = $count;
          }
          $dateCounter += 24 * 60 * 60;
          // Plus one day
          }
          $header[] = array('label' => 'modName', 'type' => 'string');
          foreach ($this->modulesInCourse as $index => $module) {
          $data[$module->locatedPluginName][] = $module->pluginName;
          }

          $rows = array();
          foreach($data as $modName => $restOfRow) {
          $row = array();
          $row[] = array('v' => $modName);
          foreach($restOfRow as $index => $value) {
          $row[] = array('v' => $value);
          }
          $rows[] = array('c' => $row);
          }

          $result['cols'] = $header;
          $result['rows'] = $rows;
          //echo "<pre>".print_r(json_encode($result), true)."</pre>";
          //echo "<pre>".print_r($result, true)."</pre>";
          return $result;
         */
    }

    public function getModStatsInCourse($modName) {
        $result = array();
        $data = array();
        $header = array();
        $dateCounter = $this->startDate;
        $header[] = "Name";
        //echo "<pre>".print_r($this->modulesInCourse, true)."</pre>";
        while ($dateCounter <= $this->endDate) {
            $printableDate = date($this->dateformat, $dateCounter);
            $header[] = $printableDate;
            $dateCounter += 24 * 60 * 60;
        }
        $dateCounter = $this->startDate;

        $modInstances = $this->modulesInCourse['mod_' . $modName]->getAllModInstancesByLog($this->course);
        //echo "<pre>".print_r($modInstances, true)."</pre>";
        //Initialize table for every instance with count = 0
        foreach ($modInstances as $contextinstanceid => $object) {
            $dateCounter = $this->startDate;
            $data[$object->name] = array();
            while ($dateCounter <= $this->endDate) {
                $data[$object->name][] = $this->modulesInCourse['mod_' . $modName]->getModStatsForInstance($contextinstanceid, $dateCounter, $dateCounter + 24 * 60 * 60 - 1);
                $dateCounter += 24 * 60 * 60;
            }
        }
        $result['header'] = $header;
        $result['data'] = $data;
        return $result;
    }

}

