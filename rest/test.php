<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

require_once '../../../config.php';

require_login();

GLOBAL $CFG, $PAGE, $CONTEXT;

$PAGE->set_context($CONTEXT);

$cloud = new activityCloud(64);
$cloud->printCloud();

class activityCloud {

    public $courseid;
    private $store;
    private $data;
    private $modinfo;
    private $sumOfEvents;
    private $FONT_SIZE_MAX = 50;
    private $FONT_SIZE_MIN = 10;
    private $STEPS = 5;

    public function __construct($courseid) {
        $this->courseid = $courseid;

        $log_manager = get_log_manager();
        $readers = $log_manager->get_readers();
        $this->store = $readers['logstore_standard'];

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
        $selectwhere = "courseid=" . $this->courseid . " AND target = 'course_module' AND component LIKE 'mod_%'";
        $params = array();
        $order = "timecreated DESC";
        $limitfrom = 0;
        $limitnum = 999999999999999999999;

        $events = $this->store->get_events_select($selectwhere, $params, $order, $limitfrom, $limitnum);

        $data = array();

        // A simple "GROUP BY" would do this job
        foreach ($events as $id => $event) {
            //$this->printArray($event);
            $cmid = $event->contextinstanceid;
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

                $data[$cmid] = $entry;
            }
            //$url = $event->get_url();
            //pr($event);
        }
        return $data;
    }

    public function printCloud() { 
       $buffer = "";

        $buffer .= '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" height="300" width="50%">';

        //$buffer .= '<text font-family="impact" x="50" y="86">';
        foreach ($this->data as $cmid => $a) {
            $weight = 10 * $a['numberOfEvents'] * $a['numberOfEvents'] / $this->sumOfEvents;
            $size = ($this->FONT_SIZE_MAX - $this->FONT_SIZE_MIN) / $this->STEPS;
            $weighted_size = $this->FONT_SIZE_MIN + floor($weight * $size);
            
            
            $buffer .= '<a xlink:href="grafik.svg">'
                    . '<tspan font-size="'. $weighted_size . '">' . $a['name'] .'</tspan>'
                    . '</a>';
            
            /*
            $buffer .= '<a xlink:href="' . $a['moodleURL'] . '"
                            xlink:title="' . $a['name'] . '">
                            <rect x="10" y="60" ry="5" width="40" height="40"
                            style="fill:mintcream; stroke:black;" />
                        </a>';
             * 
             */
            //$buffer .= '<tspan font-size="'. $weighted_size . '"><a href"' . $a['moodleURL'] . '">' . $a['name'] .'</a></tspan>';
        }
        //$buffer .= '</text>';
        $buffer .= '</svg>';
        //echo "<html><body>";
        echo $buffer;
        //echo "</body></html>";
        //echo '<svg height="300" width="100%"><text fill="#000000" font-size="45" font-family="Verdana" x="50" y="86">SVG</text>';
    }

    public function printArray($var) {
        echo "<pre>" . print_r($var, true) . "</pre>";
    }

}

?>