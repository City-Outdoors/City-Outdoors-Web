<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

require '../includes/src/global.php';


$currentUser = mustBeLoggedIn();
if (!$currentUser->isAdministrator()) die('No Access');

$event = Event::loadByID($_GET['id']);
if (!$event) die('not found');

$featureSearch = new FeatureSearch();
$featureSearch->hasEvent($event);

$tpl = getSmarty($currentUser);
$tpl->assign('event',$event);
$tpl->assign('featureSearch',$featureSearch);
$tpl->display('admin/event.htm');

