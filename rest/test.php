<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

//require_once '../../../config.php';
//GLOBAL $CFG, $PAGE, $CONTEXT;
//require_login();
//$PAGE->set_context($CONTEXT);

if (isset($_REQUEST['courseid'])) {
    require_once '../../../config.php';
    //require_login();
    global $PAGE;
    $PAGE->set_context(context_course::instance($_REQUEST['courseid']));
    $cloud = new activityCloud($_REQUEST['courseid']);
    echo $cloud->getCloud();
}

if (isset($_REQUEST['getJSONforCourse'])) {
    $courseid = $_REQUEST['getJSONforCourse'];

    require_once '../../../config.php';
    //require_login();
    global $PAGE;
    $PAGE->set_context(context_course::instance($courseid));

    $cloud = new activityCloud($courseid);
    echo $cloud->dataAsJSON();
}

class activityCloud {

    public $courseid;
    private $store;
    private $data;
    private $modinfo;
    private $sumOfEvents;
    private $FONT_SIZE_MAX = 100;
    private $FONT_SIZE_MIN = 40;
    private $STEPS = 5;
    private $STEP_WIDTH = 0;
    private $COLORS = [
        //"#DFE2DB",
        "#FFF056",
        "#C63D0F",
        "#FDF3E7",
        "#7E8F7C",
        //"#005A31",
        "#A8CD1B",
        "#CBE32D",
        "#F3FAB6",
        "#558C89",
        "#74AFAD",
        "#D9853B",
        "#ECECEA",
        "#DE1B1B",
        "#F6F6F6",
        "#E9E581",
        "#7D1935",
        "#4A96AD",
        "#F5F3EE",
        "#E44424",
        "#67BCDB",
        "#A2AB58",
        "#6DBDD6",
        "#B71427",
        "#FFE658",
        "#118C4E",
        "#C1E1A6",
        "#FF9009",
        "#DF3D82"
    ];

    public function __construct($courseid) {
        GLOBAL $CFG, $PAGE, $CONTEXT;
        //$this->printArray($CFG);
        //require_once '../../../config.php';
        //$PAGE->set_context($CONTEXT);

        $this->courseid = $courseid;

        $log_manager = get_log_manager();
        $readers = $log_manager->get_readers();

        $this->store = $readers['logstore_standard'];


        $this->STEP_WIDTH = ($this->FONT_SIZE_MAX - $this->FONT_SIZE_MIN) / 5;
        
        
        $this->modinfo = get_fast_modinfo($this->courseid);
        $this->data = $this->getData();
        $this->sumOfEvents = $this->calculateSumOfEvents();
        
    }

    private function calculateSumOfEvents() {
        $sum = 0;
        foreach ($this->data as $cmid => $activity) {
            $sum += $activity['numberOfEvents'];
        }
        return $sum;
    }

    private function getData() {
        $selectwhere = "courseid=" . $this->courseid . " AND component LIKE 'mod_%'"; // AND target = 'course_module' ";
        $params = array();
        $order = "timecreated DESC";
        $limitfrom = 0;
        $limitnum = 100;

        $events = $this->store->get_events_select($selectwhere, $params, $order, $limitfrom, $limitnum);

        $data = array();

        // A simple "GROUP BY" would do this job
        foreach ($events as $id => $event) {
            //$this->printArray($event);
            $cmid = $event->contextinstanceid;
            try {
                if (!empty($cmid)) {
                    $cm = $this->modinfo->get_cm($cmid);
                    // Is there already an entry in the data array for this activity
                    if (isset($data[$cmid])) {
                        $data[$cmid]['numberOfEvents'] += 1;
                    } else {
                        $entry = array();
                        $entry['cmid'] = $cmid;
                        $entry['name'] = $cm->name;
                        $entry['numberOfEvents'] = 1;
                        $entry['iconURL'] = $cm->get_icon_url()->out();
                        $entry['moodleURL'] = $event->get_url()->out();
                        $entry['formattedOutput'] = $cm->get_formatted_name();
                        $entry['color'] = $this->getColor($cm->module);
                        $data[$cmid] = $entry;
                    }
                }

                //$url = $event->get_url();
                //pr($event);
            } catch (Exception $exc) {
                //echo $exc->getTraceAsString();
            }
        }
        return $data;
    }

    /**
     * Returns a color by an integer
     * 
     * @param int $module the module type id
     * @return hexadecimal color, for example #FFFFFF
     */
    private function getColor($module) {
        if (isset($this->COLORS[$module])) {
            return $this->COLORS[$module];
        } else {
            return $this->COLORS[0];
        }
    }

    public function dataAsJSON() {
        if (empty($this->data)) {
            $this->data = $this->getData();
        }

        $return = array();
        foreach ($this->data as $id => $data) {
            $weigth = $data['numberOfEvents'] / $this->sumOfEvents;
            //$size = $this->FONT_SIZE_MIN + $weigth * $this->STEP_WIDTH;
            $size = $weigth * $this->FONT_SIZE_MAX;
            $data['size'] = $size;
            if ($size > 0) {
                $return[] = $data;
            }
        }
        $return = array('name' => 'root', 'children' => $return);
        return json_encode($return);
    }

    public function getCloud() {
        global $CFG;


        $this->modinfo = get_fast_modinfo($this->courseid);
        $this->data = $this->getData();
        $this->sumOfEvents = $this->calculateSumOfEvents();

        $buffer = '
            
        <script src="' . $CFG->wwwroot . '/mod/learninganalytics/js/d3.v3.min.js"></script>
        <script src="' . $CFG->wwwroot . '/mod/learninganalytics/js/jquery-2.1.4.min.js"></script>
        <script src="' . $CFG->wwwroot . '/mod/learninganalytics/js/bootstrap.min.js"></script>
        <script src="' . $CFG->wwwroot . '/mod/learninganalytics/rest/bubbles.js"></script>
        <script>
            run(' . $this->dataAsJSON() . ');
        </script>'
        ;
        //$this->printHTML($buffer);
        //echo $buffer;
        return $buffer;
    }

    public static function getCloudLoader() {
        global $CFG;
        $buffer = '
            
        <script src="' . $CFG->wwwroot . '/mod/learninganalytics/js/d3.v3.min.js"></script>
        <script src="' . $CFG->wwwroot . '/mod/learninganalytics/js/jquery-2.1.4.min.js"></script>
        <script src="' . $CFG->wwwroot . '/mod/learninganalytics/js/bootstrap.min.js"></script>
        <script src="' . $CFG->wwwroot . '/mod/learninganalytics/rest/bubbles.js"></script>
        <script>
            $.get( "/mod/learninganalytics/rest/test.php?getJSONforCourse=COURSEID", function( data ) {
                json = JSON.parse(data);
                run(json);
            });
        </script>
        <div id="learninganalytics_div"></div>
        '
        ;
        //$this->printHTML($buffer);
        //echo $buffer;
        return $buffer;
    }

    public function printHTML($var) {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = FALSE;
        libxml_use_internal_errors(true);
        $dom->loadHTML($var);
        libxml_clear_errors();
        $dom->formatOutput = TRUE;

        echo $dom->saveXML();
    }

    public function printArray($var) {
        echo "<pre>" . print_r($var, true) . "</pre>";
    }

}

?>