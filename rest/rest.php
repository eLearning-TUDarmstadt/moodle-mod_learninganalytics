<?php


require_once "Slim/Slim.php";
require_once '../../../config.php';
require_once 'observer.php';
require_once 'lib.php';

$context = context_system::instance();


global $CFG;
\Slim\Slim::registerAutoloader ();

$app = new \Slim\Slim ();
$app->map ( '/uniqueViews/:id', 'uniqueViews' )->via ( 'GET');
$app->map ('/detailedModView/:course/:modName(/:instance)(/:function)', 'detailedModView')->via ('GET');
$app->run ();

function uniqueViews($courseID) {
	$observer = new observer($courseID);
	echo json_encode($observer -> collectUniqueViews());
}

/*
 * Gives stats about this module in the course or - if given - about a special mod instance
 */
function detailedModView($courseID, $modName, $instance = -1, $function = false) {
        $modName = "mod_".$modName;
        require_once $modName.'.php';
        $mod = new $modName($courseID);
        if($instance < 0) {
            echo json_encode($mod->getModStatsInCourse($courseID));
        }
        else {
            if($function) {
                echo json_encode($mod->$function($courseID, $instance));
            }
            else {
                echo json_encode($mod->getModStatsForInstance($courseID, $instance));
            }
        }
}

?>