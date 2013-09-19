<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */
require 'includes/src/global.php';


$event = Event::loadByID($_GET['id']);
if (!$event) {
	 die('Not found!');
}

$eventSearch = new EventSearch();
$eventSearch->setAfterNow();
$eventSearch->setPaging(1, $CONFIG->EVENT_PAGE_SHOW_FUTURE_EVENTS);
	
$featureSearch = new FeatureSearch();
$featureSearch->hasEvent($event);

$tpl = getSmarty();
$tpl->assign('eventSearch',$eventSearch);
$tpl->assign('featureSearch',$featureSearch);
$tpl->assign('event',$event);
$tpl->display('event.htm');







