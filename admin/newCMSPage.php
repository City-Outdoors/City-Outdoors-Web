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


if (isset($_POST) && isset($_POST['slug']) && $_POST['CSFRToken'] == $_SESSION['CSFRToken']) {
	$pageID = CMSContent::createPage($_POST['slug'],$_POST['title'],$currentUser);
	header("Location: /admin/editCMSPage.php?id=".$pageID);
	die();
}


$tpl = getSmarty($currentUser);
$tpl->display('admin/newCMSPage.htm');


