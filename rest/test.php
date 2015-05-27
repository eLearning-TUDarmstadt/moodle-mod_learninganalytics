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
    private $FONT_SIZE_MAX = 100;
    private $FONT_SIZE_MIN = 40;
    private $STEPS = 5;
    private $STEP_WIDTH = 0;

    public function __construct($courseid) {
        $this->courseid = $courseid;

        $log_manager = get_log_manager();
        $readers = $log_manager->get_readers();
        $this->store = $readers['logstore_standard'];

        $this->modinfo = get_fast_modinfo($this->courseid);

        $this->data = $this->getData();

        $this->sumOfEvents = $this->calculateSumOfEvents();
        
        $this->STEP_WIDTH = ($this->FONT_SIZE_MAX - $this->FONT_SIZE_MIN) / 5;
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

    public function dataAsJSON() {
        $return = array();
        foreach ($this->data as $id => $data) {
            $weigth = $data['numberOfEvents'] / $this->sumOfEvents;
            //$size = $this->FONT_SIZE_MIN + $weigth * $this->STEP_WIDTH;
            $size = $weigth * $this->FONT_SIZE_MAX;
            $data['size'] = $size;
            if($size > 0) {
                $return[] = $data;
            }
        }
        $return = array('name' => 'root', 'children' => $return);
        return json_encode($return);
    }

    public function printCloud() {
        global $CFG;
        $buffer = "";
        $buffer .= '
                <!DOCTYPE html>
                <meta charset="utf-8">
                <style>

                text {
                  font: 10px sans-serif;
                }

                </style>
                <body>
                <script src="' . $CFG->wwwroot .'/mod/learninganalytics/js/d3.v3.min.js"></script>
                <script>
                var diameter = 600,
                    format = d3.format(",d"),
                    color = d3.scale.category20c();

                var bubble = d3.layout.pack()
                    .sort(null)
                    .size([diameter, diameter])
                    .padding(1.5);

                var svg = d3.select("body").append("svg")
                    .attr("width", diameter)
                    .attr("height", diameter)
                    .attr("class", "bubble");
                    
                var json = ' . $this->dataAsJSON() . ';

                //d3.json(json, 
                var process = function(error, root) {
                  var node = svg.selectAll(".node")
                      .data(bubble.nodes(classes(root))
                      .filter(function(d) { return !d.children; }))
                    .enter().append("g")
                      .attr("class", "node")
                      .attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });

                  node.append("title")
                      .text(function(d) { return d.className + ": " + format(d.value); });

                  node.append("circle")
                      .attr("r", function(d) { return d.r; })
                      .style("fill", function(d) { return color(d.packageName); });
                 
                    /*
                  var innerSVG = node.append("g")
                        .attr("transform", function(d) { return "translate(" + 0.25*1,8*d.r + ")"; })
                        .attr("width", function(d) { return 1.8*d.r; })
                        .attr("heigth", function(d) { return 1.8*d.r; });
                  */
                  node.append("text")
                        .attr("id", function (d) { return "modTitle_" + d.originData.cmid;})
                        .style("text-anchor", "middle")
                        .text(function(d) { console.log(d); return d.className.substring(0, d.r / 3); });
                   
                  node.append("image")
                        .attr("xlink:href", function (d) { return d.originData.iconURL;})
                        .attr("x", function(d) {
                            var text = d3.select("#modTitle_" + d.originData.cmid);
                            console.log(text[0][0]);
                            var width = text[0][0].clientWidth;
                            return -0.5*width - 28;
                            }
                        )
                        .attr("y", -24)
                        .attr("height", 24)
                        .attr("width", 24);
               }
                process(null, json);
                // Returns a flattened hierarchy containing all leaf nodes under the root.
                function classes(root) {
                  var classes = [];

                  function recurse(name, node) {
                    if (node.children) node.children.forEach(function(child) { recurse(node.name, child); });
                    else classes.push({packageName: name, className: node.name, value: node.size, originData: node});
                  }
 
                  recurse(null, root);
                  return {children: classes};
                }

                d3.select(self.frameElement).style("height", diameter + "px");

                </script>';
        //$this->printHTML($buffer);
        echo $buffer;
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