<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
include dirname(__FILE__).'/../src/global.php';


$config = json_decode(file_get_contents($argv[1]));
if (!is_object($config)) die("Config failed to load\n");

$user = User::loadByEmail($config->destination->userEmail);
if (!$user) die("No User found\n");

$monthNames = array(
	1=>'January',
	2=>'Februrary',
	3=>'March',
	4=>'April',
	5=>'May',
	6=>'June',
	7=>'July',
	8=>'August',
	9=>'September',
	10=>'October',
	11=>'November',
	12=>'December',
);
$currentMonth = date("m");
$currentYear = date("Y");

$localTimeZone = new DateTimeZone($CONFIG->LOCAL_TIME_ZONE);

for ($month = 1; $month <= 12; $month++) {

	$utc = new DateTimeZone("UTC");
	
	if ($month < $currentMonth) {
		$year = $currentYear + 1;
	} else {
		$year = $currentYear;
	}
	
	$start = new DateTime("",$utc);
	$start->setTime(0, 0, 0);
	$start->setDate($year, $month, 1);
	
	$end = new DateTime("",$utc);
	$end->setTime(0, 0, 0);
	if ($month == 12) {
		$end->setDate($year+1, 1, 1);
	} else {
		$end->setDate($year, $month+1, 1);
	}
	
	print "Year ".$year." Month ".$month." \n";
	//var_dump($start);	var_dump($end);
	
	$html = "<h2>".$monthNames[$month]." ".$year."</h2>";
	
	$eventSearch = new EventSearch();
	$eventSearch->setAfter($start);
	$eventSearch->setBefore($end);
	if($eventSearch->num()) {
		while($event = $eventSearch->nextResult()) {
			$html .= "<h3>".htmlspecialchars($event->getTitle())."</h3>";
			$html .= "<p>".htmlspecialchars($event->getDescriptionText())."</p>";

			$eventStartAt = clone $event->getStartAt();
			$eventStartAt->setTimezone($localTimeZone);
			$eventEndAt = clone $event->getStartAt();
			$eventEndAt->setTimezone($localTimeZone);
			$html .= "<p>".$eventStartAt->format("g:ia D jS M Y")." to ".$eventEndAt->format("g:ia D jS M Y")."</p>";


		}
	} else {
		$html .= "<p>".$config->noevents->html."</p>";
	}
	
	
	$cmscontent = CMSContent::loadBlockBySlug("whatson-".$month);
	if (!$cmscontent) $cmscontent = CMSContent::createBlock ("whatson-".$month, $user);
	
	if ($cmscontent->getLatestVersionHTML() != $html) {
		$cmscontent->newVersion($html, $user);
		$cmscontent->setImported(true);
	}
	
}


