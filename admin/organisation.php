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


$organisation = Organisation::loadByID($_GET['id']);
if (!$organisation) die('not found');


$tpl = getSmarty($currentUser);
$tpl->assign('organisation', $organisation);
$tpl->display('admin/organisation.htm');

