<?php
/**
 * @author James Baster  <james@jarofgreen.co.uk>
 * @copyright City of Edinburgh Council & James Baster
 * @license Open Source under the 3-clause BSD License
 * @url https://github.com/City-Outdoors/City-Outdoors-Web
 */

require '../includes/src/global.php';


$currentUser = mustBeLoggedIn();
if (!$currentUser->isSystemAdministrator()) die('No Access');

if (isset($_POST['CSFRToken']) && $_POST['action'] == 'newOrganisation' && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$organisation = Organisation::create($_POST['title'], $_POST['description'], $currentUser);
	header("Location: /admin/organisation.php?id=".$organisation->getId());
	die();
}


$tpl = getSmarty($currentUser);
$tpl->display('admin/newOrganisation.htm');

