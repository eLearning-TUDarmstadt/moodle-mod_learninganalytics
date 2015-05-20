<?php
/************************************************************************
  			mod.php - Copyright 

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
The original location of this file is C:/Users/bs42cavo/uml-generated-code/mod.php
**************************************************************************/

require_once 'mod.php';
require_once 'iface_mod.php';
require_once '../../../config.php';


/**
 * class mod
 * 
 */
class mod
{
		public $locatedPluginName;
                public $pluginName;
		/** Aggregations: */

		/** Compositions: */

		 /*** Attributes: ***/
		public function __construct() {
			$this->locatedPluginName = $this->getLocatedName();
                        $this->pluginName = str_replace('mod_', '', get_called_class());
			//echo $this->locatedPluginName;
		}

		/**
		 * 
		 *
		 * @param int course 
		 * @param long startDate 
		 * @param long endDate 
		 * @return int
		 * @access public
		 */		 
		public function getUniqueViews( $course,  $startDate,  $endDate ) {
			GLOBAL $DB;
			$sql = "SELECT
						userid
				FROM
					{logstore_standard_log}
				WHERE
					action = 'viewed' AND
					component = '".get_called_class()."' AND
					courseid = ".$course." AND
					timecreated >= ".$startDate." AND
					timecreated <= ".$endDate."
				GROUP BY userid
				";
			
			$result = $DB->get_records_sql($sql);
			//echo $sql."<br /> => <pre>".print_r($result,true)."</pre>";
			return count($result);
		} // end of member function getUniqueViews

		public function getClassName() {
			return get_called_class();
		} // end of mod
		
		private function getLocatedName() {
			$mod = str_replace('mod_', '', get_called_class());
			$PluginName = get_string('pluginname', $mod);
			return $PluginName;
		}
                
//                public function getModStatsForInstance($contextinstanceid, $startDate, $endDate) {
//                    GLOBAL $DB;
//                    $sql = "SELECT
//                                COUNT(userid) as count
//                            FROM {logstore_standard_log}
//                            WHERE
//                                action = 'viewed' AND
//                                component = '".get_called_class()."' AND
//                                contextinstanceid = ".$contextinstanceid." AND
//				timecreated >= ".$startDate." AND
//				timecreated <= ".$endDate."
//                            GROUP BY
//                                    userid, contextinstanceid";
//                    $result = $DB->get_record_sql($sql);
//                    
//                    if(empty($result)) {
//                        return 0;
//}
//                    else {
//                        return (int)$result->count;
//                    }
//                }
                
                public function getAllModInstancesByLog($course) {
                    GLOBAL $DB;
                    $sql = "SELECT
                                contextinstanceid,
                                (SELECT name FROM mdl_forum WHERE id=(SELECT instance FROM {course_modules} WHERE id=contextinstanceid)) AS name
                            FROM {logstore_standard_log}
                            WHERE
                                action = 'viewed' AND
                                component = '".get_called_class()."' AND
                                courseid = ".$course."
                            GROUP BY
                                contextinstanceid";
                    return $DB->get_records_sql($sql);
                    
                }
}
?>
