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

$s = new EventSearch;
$s->includeDeleted(true);

$tpl = getSmarty($currentUser);
$tpl->assign('eventSearch',$s);
$tpl->display('admin/listEvents.htm');

