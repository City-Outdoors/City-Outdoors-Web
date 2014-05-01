<?php
/**
 * 
 * Sample Config JSON:
 * 
 * {
 * 	"title": "demoHAS",
 * 	"url": "http://demo.hasacalendar.co.uk/event/json",
 * 	"destination": {
 * 		"userEmail":"james@example.com"
 * 	},
 * 	"searchCollection": {
 * 		"slug":"collectian1"
 * 	}
 * }
 * 
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
include dirname(__FILE__).'/../src/global.php';
include dirname(__FILE__).'/importFunctions.php';


$config = json_decode(file_get_contents($argv[1]));
if (!is_object($config)) die("Config failed to load\n");

$user = User::loadByEmail($config->destination->userEmail);
if (!$user) die("No User found\n");

$searchCollection = null;
if ($config->searchCollection->slug) {
	$searchCollection = Collection::loadBySlug($config->searchCollection->slug);
}

$import = new ImportEventHasACalendarJSON($config->title, $config->url, $user, $searchCollection);
$import->import();

print "Done\n";


